<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\CommandConflictException;
use App\Exceptions\GameRuleViolationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PlaceBuildingRequest;
use App\Models\Building;
use App\Models\BuildingDefinition;
use App\Models\GameActionLog;
use App\Models\GameCommand;
use App\Models\GameWorld;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BuildingCommandController extends Controller
{
    private const MAP_WIDTH = 20;

    private const MAP_HEIGHT = 20;

    public function place(PlaceBuildingRequest $request, GameWorld $world): JsonResponse
    {
        abort_if($world->user_id !== $request->user()->id, 403, '이 월드에 접근할 권한이 없습니다.');

        $commandId = $request->validated('commandId');

        $existing = GameCommand::where('game_world_id', $world->id)
            ->where('command_id', $commandId)
            ->first();

        if ($existing) {
            return response()->json($existing->response_payload, $existing->status === 'completed' ? 200 : 422);
        }

        $payload = $request->validated('payload');
        $buildingType = BuildingDefinition::where('code', $payload['buildingType'])->firstOrFail();
        $levelOne = $buildingType->levels()->where('level', 1)->firstOrFail();

        try {
            $response = DB::transaction(function () use ($request, $world, $commandId, $payload, $buildingType, $levelOne) {
                /** @var GameWorld $lockedWorld */
                $lockedWorld = GameWorld::whereKey($world->id)->lockForUpdate()->firstOrFail();

                if ($lockedWorld->revision !== (int) $request->validated('baseRevision')) {
                    throw new CommandConflictException($lockedWorld->revision);
                }

                $this->assertPlacementIsFree($lockedWorld, (int) $payload['x'], (int) $payload['y'], $buildingType);
                $updatedResources = $this->chargeResources($lockedWorld, $levelOne->build_cost ?? []);

                $building = $lockedWorld->buildings()->create([
                    'building_type_id' => $buildingType->id,
                    'x' => $payload['x'],
                    'y' => $payload['y'],
                    'rotation' => $payload['rotation'],
                    'level' => 1,
                    'state' => 'constructing',
                    'started_at' => now(),
                    'finishes_at' => now()->addSeconds($buildingType->base_build_time),
                ]);

                $lockedWorld->increment('revision');
                $lockedWorld->refresh();

                $result = [
                    'success' => true,
                    'revision' => $lockedWorld->revision,
                    'serverTime' => now()->toIso8601String(),
                    'changes' => [
                        'resources' => $updatedResources,
                        'buildings' => [[
                            'id' => $building->id,
                            'type' => $buildingType->code,
                            'x' => $building->x,
                            'y' => $building->y,
                            'level' => $building->level,
                            'state' => $building->state,
                        ]],
                    ],
                ];

                GameCommand::create([
                    'game_world_id' => $world->id,
                    'user_id' => $request->user()->id,
                    'command_id' => $commandId,
                    'command_type' => 'building.place',
                    'base_revision' => $request->validated('baseRevision'),
                    'status' => 'completed',
                    'request_payload' => $request->validated(),
                    'response_payload' => $result,
                    'completed_at' => now(),
                ]);

                GameActionLog::create([
                    'game_world_id' => $world->id,
                    'user_id' => $request->user()->id,
                    'action_type' => 'building.place',
                    'target_type' => 'building',
                    'target_id' => $building->id,
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

    private function assertPlacementIsFree(GameWorld $world, int $x, int $y, BuildingDefinition $type): void
    {
        $left = $x;
        $right = $x + $type->width - 1;
        $top = $y;
        $bottom = $y + $type->height - 1;

        if ($right >= self::MAP_WIDTH || $bottom >= self::MAP_HEIGHT) {
            throw new GameRuleViolationException('건물이 맵 범위를 벗어납니다.');
        }

        $overlaps = $world->buildings()
            ->with('buildingType')
            ->get()
            ->contains(function (Building $existing) use ($left, $right, $top, $bottom) {
                $existingRight = $existing->x + $existing->buildingType->width - 1;
                $existingBottom = $existing->y + $existing->buildingType->height - 1;

                return $left <= $existingRight && $right >= $existing->x
                    && $top <= $existingBottom && $bottom >= $existing->y;
            });

        if ($overlaps) {
            throw new GameRuleViolationException('이미 다른 건물이 있는 타일입니다.');
        }
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
