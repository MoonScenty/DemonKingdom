<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\GameRuleViolationException;
use App\Http\Controllers\Api\V1\Concerns\HandlesGameCommands;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AssignResidentRequest;
use App\Http\Requests\Api\V1\RecruitResidentRequest;
use App\Http\Requests\Api\V1\UnassignResidentRequest;
use App\Models\Building;
use App\Models\GameWorld;
use App\Models\Resident;
use App\Models\ResidentDefinition;
use App\Services\WorldProductionProcessor;
use Illuminate\Http\JsonResponse;

class ResidentCommandController extends Controller
{
    use HandlesGameCommands;

    /**
     * 종족별 모집 비용(금화). README에 수치가 정해져 있지 않아 슬라임 특유의
     * "유지비가 낮다"는 컨셉만 반영해 임시로 정한 값이다.
     */
    public const RECRUIT_COST = [
        'slime' => 30,
        'goblin' => 50,
        'ogre' => 80,
    ];

    public function __construct(private readonly WorldProductionProcessor $processor)
    {
    }

    public function recruit(RecruitResidentRequest $request, GameWorld $world): JsonResponse
    {
        $payload = $request->validated('payload');
        $race = $payload['race'];
        $definition = ResidentDefinition::where('race', $race)->firstOrFail();
        $cost = self::RECRUIT_COST[$race] ?? 50;

        return $this->runCommand($world, $request, 'resident.recruit', 'resident', function (GameWorld $lockedWorld) use ($definition, $race, $cost) {
            $this->assertHousingHasRoom($lockedWorld);
            $updatedResources = $this->chargeResources($lockedWorld, ['gold' => $cost]);

            $sequence = $lockedWorld->residents()->where('resident_type_id', $definition->id)->count() + 1;

            $resident = $lockedWorld->residents()->create([
                'resident_type_id' => $definition->id,
                'name' => "{$definition->race}-{$sequence}",
                'level' => 1,
                'experience' => 0,
                'loyalty' => 50,
                'health_state' => 'healthy',
                'current_state' => 'idle',
                'assigned_building_id' => null,
            ]);

            return [
                'targetId' => $resident->id,
                'changes' => [
                    'resources' => $updatedResources,
                    'residents' => [$this->serializeResident($resident, $race)],
                ],
            ];
        });
    }

    public function assign(AssignResidentRequest $request, GameWorld $world, Resident $resident): JsonResponse
    {
        $this->assertResidentBelongsToWorld($resident, $world);
        $payload = $request->validated('payload');

        return $this->runCommand($world, $request, 'resident.assign', 'resident', function (GameWorld $lockedWorld) use ($resident, $payload) {
            $building = $lockedWorld->buildings()->with('buildingType')->find($payload['buildingId']);

            if (! $building) {
                throw new GameRuleViolationException('배정할 건물을 찾을 수 없습니다.');
            }

            if ($building->state !== 'active') {
                throw new GameRuleViolationException('가동 중인 건물에만 주민을 배정할 수 있습니다.');
            }

            $this->assertBuildingHasWorkerRoom($building);

            $resident->refresh();
            $resident->update([
                'assigned_building_id' => $building->id,
                'current_state' => 'working',
            ]);

            return [
                'targetId' => $resident->id,
                'changes' => [
                    'residents' => [$this->serializeResident($resident, $resident->residentType->race)],
                ],
            ];
        });
    }

    public function unassign(UnassignResidentRequest $request, GameWorld $world, Resident $resident): JsonResponse
    {
        $this->assertResidentBelongsToWorld($resident, $world);

        return $this->runCommand($world, $request, 'resident.unassign', 'resident', function () use ($resident) {
            $resident->refresh();
            $resident->update([
                'assigned_building_id' => null,
                'current_state' => 'idle',
            ]);

            return [
                'targetId' => $resident->id,
                'changes' => [
                    'residents' => [$this->serializeResident($resident, $resident->residentType->race)],
                ],
            ];
        });
    }

    private function assertResidentBelongsToWorld(Resident $resident, GameWorld $world): void
    {
        abort_if($resident->game_world_id !== $world->id, 404);
    }

    private function assertHousingHasRoom(GameWorld $world): void
    {
        $capacity = $world->buildings()
            ->whereHas('buildingType', fn ($query) => $query->where('code', 'house'))
            ->where('state', 'active')
            ->with('buildingType.levels')
            ->get()
            ->sum(fn (Building $house) => $house->buildingType->levels->firstWhere('level', $house->level)?->worker_capacity ?? 0);

        if ($world->residents()->count() >= $capacity) {
            throw new GameRuleViolationException('주택 공간이 부족합니다.');
        }
    }

    private function assertBuildingHasWorkerRoom(Building $building): void
    {
        $capacity = $building->buildingType->levels->firstWhere('level', $building->level)?->worker_capacity ?? 0;
        $assigned = Resident::where('assigned_building_id', $building->id)->count();

        if ($assigned >= $capacity) {
            throw new GameRuleViolationException('해당 건물에 더 이상 주민을 배정할 수 없습니다.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeResident(Resident $resident, string $race): array
    {
        return [
            'id' => $resident->id,
            'residentType' => $race,
            'name' => $resident->name,
            'level' => $resident->level,
            'experience' => $resident->experience,
            'loyalty' => $resident->loyalty,
            'currentState' => $resident->current_state,
            'assignedBuildingId' => $resident->assigned_building_id,
        ];
    }
}
