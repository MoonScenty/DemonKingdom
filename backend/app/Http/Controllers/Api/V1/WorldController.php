<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BuildingDefinition;
use App\Models\GameWorld;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorldController extends Controller
{
    private const MAP_WIDTH = 20;

    private const MAP_HEIGHT = 20;

    private const STARTING_RESOURCES = [
        'gold' => ['amount' => 100, 'capacity' => 1000],
        'food' => ['amount' => 50, 'capacity' => 500],
        'wood' => ['amount' => 50, 'capacity' => 500],
        'ore' => ['amount' => 0, 'capacity' => 500],
        'mana' => ['amount' => 0, 'capacity' => 200],
    ];

    public function index(Request $request): JsonResponse
    {
        $worlds = GameWorld::query()->where('user_id', $request->user()->id)->get();

        return response()->json($worlds->map(fn (GameWorld $world) => $this->summarize($world))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $world = $this->createWorldForUser($request->user());

        return response()->json($this->summarize($world), 201);
    }

    public function show(Request $request, GameWorld $world): JsonResponse
    {
        $this->authorizeOwner($request, $world);

        return response()->json($this->summarize($world));
    }

    public function state(Request $request, GameWorld $world): JsonResponse
    {
        $this->authorizeOwner($request, $world);
        $this->loadWorldRelations($world);

        return response()->json($this->serializeState($world));
    }

    public function changes(Request $request, GameWorld $world): JsonResponse
    {
        $this->authorizeOwner($request, $world);

        $afterRevision = (int) $request->query('afterRevision', 0);

        if ($world->revision <= $afterRevision) {
            return response()->json(['revision' => $world->revision]);
        }

        // 필드 단위 변경분 추적은 9단계(최적화)에서 다룬다. 지금은 전체 상태를 안전하게 반환한다.
        $this->loadWorldRelations($world);

        return response()->json($this->serializeState($world));
    }

    private function authorizeOwner(Request $request, GameWorld $world): void
    {
        abort_if($world->user_id !== $request->user()->id, 403, '이 월드에 접근할 권한이 없습니다.');
    }

    private function loadWorldRelations(GameWorld $world): void
    {
        $world->load(['resources', 'buildings.buildingType', 'residents.residentType', 'quests', 'events']);
    }

    private function createWorldForUser(User $user): GameWorld
    {
        return DB::transaction(function () use ($user) {
            $world = GameWorld::create([
                'user_id' => $user->id,
                'name' => "{$user->name}의 마왕국",
                'city_level' => 1,
                'population' => 0,
                'current_era' => 'foundation',
                'revision' => 0,
                'last_processed_at' => now(),
                'last_active_at' => now(),
            ]);

            foreach (self::STARTING_RESOURCES as $type => $spec) {
                $world->resources()->create([
                    'resource_type' => $type,
                    'amount' => $spec['amount'],
                    'capacity' => $spec['capacity'],
                ]);
            }

            $castleType = BuildingDefinition::where('code', 'castle')->first();

            if ($castleType) {
                $world->buildings()->create([
                    'building_type_id' => $castleType->id,
                    'x' => intdiv(self::MAP_WIDTH, 2) - 1,
                    'y' => intdiv(self::MAP_HEIGHT, 2) - 1,
                    'rotation' => 0,
                    'level' => 1,
                    'state' => 'ruin',
                ]);
            }

            return $world;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function summarize(GameWorld $world): array
    {
        return [
            'id' => $world->id,
            'name' => $world->name,
            'cityLevel' => $world->city_level,
            'population' => $world->population,
            'currentEra' => $world->current_era,
            'revision' => $world->revision,
            'lastProcessedAt' => optional($world->last_processed_at)->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeState(GameWorld $world): array
    {
        return array_merge($this->summarize($world), [
            'resources' => $world->resources->map(fn ($resource) => [
                'resourceType' => $resource->resource_type,
                'amount' => $resource->amount,
                'capacity' => $resource->capacity,
            ])->values(),
            'buildings' => $world->buildings->map(fn ($building) => [
                'id' => $building->id,
                'buildingType' => $building->buildingType->code,
                'x' => $building->x,
                'y' => $building->y,
                'rotation' => $building->rotation,
                'level' => $building->level,
                'state' => $building->state,
                'startedAt' => optional($building->started_at)->toIso8601String(),
                'finishesAt' => optional($building->finishes_at)->toIso8601String(),
            ])->values(),
            'residents' => $world->residents->map(fn ($resident) => [
                'id' => $resident->id,
                'residentType' => $resident->residentType->race,
                'name' => $resident->name,
                'level' => $resident->level,
                'experience' => $resident->experience,
                'loyalty' => $resident->loyalty,
                'currentState' => $resident->current_state,
                'assignedBuildingId' => $resident->assigned_building_id,
            ])->values(),
            'quests' => $world->quests->map(fn ($quest) => [
                'id' => $quest->id,
                'questId' => $quest->quest_id,
                'status' => $quest->status,
                'progress' => $quest->progress,
                'completedAt' => optional($quest->completed_at)->toIso8601String(),
                'rewardedAt' => optional($quest->rewarded_at)->toIso8601String(),
            ])->values(),
            'events' => $world->events->map(fn ($event) => [
                'id' => $event->id,
                'eventType' => $event->event_type,
                'status' => $event->status,
                'payload' => $event->payload ?? [],
                'occurredAt' => optional($event->occurred_at)->toIso8601String(),
                'expiresAt' => optional($event->expires_at)->toIso8601String(),
                'resolvedAt' => optional($event->resolved_at)->toIso8601String(),
            ])->values(),
        ]);
    }
}
