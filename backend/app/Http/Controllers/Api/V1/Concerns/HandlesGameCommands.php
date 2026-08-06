<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Exceptions\CommandConflictException;
use App\Exceptions\GameRuleViolationException;
use App\Models\GameActionLog;
use App\Models\GameCommand;
use App\Models\GameWorld;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * 명령 처리 공통 골격: 인증/멱등성 확인 → 트랜잭션 + 월드 행 잠금 → 생산 처리 캐치업 →
 * revision 검사 → 실제 처리 → 명령/행동 로그 기록 → revision 증가.
 *
 * 이 트레이트를 사용하는 컨트롤러는 생성자에서 WorldProductionProcessor $processor를 주입해야 한다.
 */
trait HandlesGameCommands
{
    private function runCommand(
        GameWorld $world,
        FormRequest $request,
        string $commandType,
        string $targetType,
        Closure $apply,
    ): JsonResponse {
        abort_if($world->user_id !== $request->user()->id, 403, '이 월드에 접근할 권한이 없습니다.');

        $commandId = $request->validated('commandId');

        $existing = GameCommand::where('game_world_id', $world->id)
            ->where('command_id', $commandId)
            ->first();

        if ($existing) {
            return response()->json($existing->response_payload, $existing->status === 'completed' ? 200 : 422);
        }

        try {
            $response = DB::transaction(function () use ($request, $world, $commandId, $commandType, $targetType, $apply) {
                /** @var GameWorld $lockedWorld */
                $lockedWorld = GameWorld::whereKey($world->id)->lockForUpdate()->firstOrFail();
                $this->processor->process($lockedWorld);
                $lockedWorld->refresh();

                if ($lockedWorld->revision !== (int) $request->validated('baseRevision')) {
                    throw new CommandConflictException($lockedWorld->revision);
                }

                $outcome = $apply($lockedWorld);

                $lockedWorld->increment('revision');
                $lockedWorld->refresh();

                $result = [
                    'success' => true,
                    'revision' => $lockedWorld->revision,
                    'serverTime' => now()->toIso8601String(),
                    'changes' => $outcome['changes'],
                ];

                GameCommand::create([
                    'game_world_id' => $world->id,
                    'user_id' => $request->user()->id,
                    'command_id' => $commandId,
                    'command_type' => $commandType,
                    'base_revision' => $request->validated('baseRevision'),
                    'status' => 'completed',
                    'request_payload' => $request->validated(),
                    'response_payload' => $result,
                    'completed_at' => now(),
                ]);

                GameActionLog::create([
                    'game_world_id' => $world->id,
                    'user_id' => $request->user()->id,
                    'action_type' => $commandType,
                    'target_type' => $targetType,
                    'target_id' => $outcome['targetId'],
                    'before_payload' => null,
                    'after_payload' => $result['changes'],
                    'ip_address' => $request->ip(),
                ]);

                return $result;
            });
        } catch (CommandConflictException $exception) {
            return response()->json(['success' => false, 'revision' => $exception->latestRevision], 409);
        } catch (GameRuleViolationException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($response);
    }

    /**
     * @param  array<string, int>  $cost
     * @return array<string, int>
     */
    private function chargeResources(GameWorld $world, array $cost): array
    {
        $resources = $world->resources()->get()->keyBy('resource_type');
        $updated = [];

        foreach ($cost as $type => $amount) {
            $resource = $resources->get($type);

            if (! $resource || $resource->amount < $amount) {
                throw new GameRuleViolationException("{$type} 자원이 부족합니다.");
            }
        }

        foreach ($cost as $type => $amount) {
            $resource = $resources->get($type);
            $resource->decrement('amount', $amount);
            $updated[$type] = $resource->fresh()->amount;
        }

        return $updated;
    }
}
