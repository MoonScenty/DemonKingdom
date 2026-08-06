<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GameRuleViolationException;
use App\Http\Controllers\Api\V1\Concerns\HandlesGameCommands;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CollectBuildingRequest;
use App\Http\Requests\Api\V1\MoveBuildingRequest;
use App\Http\Requests\Api\V1\PlaceBuildingRequest;
use App\Http\Requests\Api\V1\RemoveBuildingRequest;
use App\Models\Building;
use App\Models\BuildingDefinition;
use App\Models\GameWorld;
use App\Services\WorldProductionProcessor;
use Illuminate\Http\JsonResponse;

class BuildingCommandController extends Controller
{
    use HandlesGameCommands;

    private const MAP_WIDTH = 20;

    private const MAP_HEIGHT = 20;

    public function __construct(private readonly WorldProductionProcessor $processor)
    {
    }

    public function place(PlaceBuildingRequest $request, GameWorld $world): JsonResponse
    {
        $payload = $request->validated('payload');
        $buildingType = BuildingDefinition::where('code', $payload['buildingType'])->firstOrFail();
        $levelOne = $buildingType->levels()->where('level', 1)->firstOrFail();

        return $this->runCommand($world, $request, 'building.place', 'building', function (GameWorld $lockedWorld) use ($payload, $buildingType, $levelOne) {
            $this->assertPlacementIsFree($lockedWorld, (int) $payload['x'], (int) $payload['y'], $buildingType, null);
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

            return [
                'targetId' => $building->id,
                'changes' => [
                    'resources' => $updatedResources,
                    'buildings' => [$this->serializeBuilding($building, $buildingType->code)],
                ],
            ];
        });
    }

    public function move(MoveBuildingRequest $request, GameWorld $world, Building $building): JsonResponse
    {
        $this->assertBuildingBelongsToWorld($building, $world);
        $payload = $request->validated('payload');

        return $this->runCommand($world, $request, 'building.move', 'building', function (GameWorld $lockedWorld) use ($payload, $building) {
            $building->refresh();
            $buildingType = $building->buildingType;

            $this->assertPlacementIsFree($lockedWorld, (int) $payload['x'], (int) $payload['y'], $buildingType, $building->id);

            $building->update([
                'x' => $payload['x'],
                'y' => $payload['y'],
                'rotation' => $payload['rotation'],
            ]);

            return [
                'targetId' => $building->id,
                'changes' => [
                    'buildings' => [$this->serializeBuilding($building, $buildingType->code)],
                ],
            ];
        });
    }

    public function remove(RemoveBuildingRequest $request, GameWorld $world, Building $building): JsonResponse
    {
        $this->assertBuildingBelongsToWorld($building, $world);

        return $this->runCommand($world, $request, 'building.remove', 'building', function () use ($building) {
            $buildingType = $building->buildingType;

            if ($buildingType->code === 'castle') {
                throw new GameRuleViolationException('마왕성은 철거할 수 없습니다.');
            }

            $buildingId = $building->id;
            $building->delete();

            return [
                'targetId' => $buildingId,
                'changes' => [
                    'removedBuildingIds' => [$buildingId],
                ],
            ];
        });
    }

    public function collect(CollectBuildingRequest $request, GameWorld $world, Building $building): JsonResponse
    {
        $this->assertBuildingBelongsToWorld($building, $world);

        return $this->runCommand($world, $request, 'building.collect', 'building', function (GameWorld $lockedWorld) use ($building) {
            $production = $building->productions()->where('is_active', true)->first();

            if (! $production || $production->stored_amount <= 0) {
                throw new GameRuleViolationException('수거할 생산물이 없습니다.');
            }

            $recipe = $production->recipe;
            $resource = $lockedWorld->resources()->where('resource_type', $recipe->output_resource_type)->first();

            if (! $resource) {
                throw new GameRuleViolationException('해당 자원을 저장할 창고가 없습니다.');
            }

            $room = max(0, $resource->capacity - $resource->amount);
            $collected = min($production->stored_amount, $room);

            $resource->increment('amount', $collected);
            $production->decrement('stored_amount', $collected);

            return [
                'targetId' => $building->id,
                'changes' => [
                    'resources' => [$recipe->output_resource_type => $resource->fresh()->amount],
                    'production' => [
                        'buildingId' => $building->id,
                        'resourceType' => $recipe->output_resource_type,
                        'collectedAmount' => $collected,
                        'storedAmount' => $production->fresh()->stored_amount,
                    ],
                ],
            ];
        });
    }

    private function assertBuildingBelongsToWorld(Building $building, GameWorld $world): void
    {
        abort_if($building->game_world_id !== $world->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeBuilding(Building $building, string $code): array
    {
        return [
            'id' => $building->id,
            'type' => $code,
            'x' => $building->x,
            'y' => $building->y,
            'rotation' => $building->rotation,
            'level' => $building->level,
            'state' => $building->state,
            'startedAt' => $building->started_at?->toIso8601String(),
            'finishesAt' => $building->finishes_at?->toIso8601String(),
        ];
    }

    private function assertPlacementIsFree(
        GameWorld $world,
        int $x,
        int $y,
        BuildingDefinition $type,
        ?int $excludeBuildingId,
    ): void {
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
            ->contains(function (Building $existing) use ($left, $right, $top, $bottom, $excludeBuildingId) {
                if ($excludeBuildingId !== null && $existing->id === $excludeBuildingId) {
                    return false;
                }

                $existingRight = $existing->x + $existing->buildingType->width - 1;
                $existingBottom = $existing->y + $existing->buildingType->height - 1;

                return $left <= $existingRight && $right >= $existing->x
                    && $top <= $existingBottom && $bottom >= $existing->y;
            });

        if ($overlaps) {
            throw new GameRuleViolationException('이미 다른 건물이 있는 타일입니다.');
        }
    }
}
