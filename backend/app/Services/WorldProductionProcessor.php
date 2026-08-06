<?php

namespace App\Services;

use App\Models\Building;
use App\Models\BuildingLevelDefinition;
use App\Models\BuildingProduction;
use App\Models\GameWorld;
use App\Models\ProductionRecipe;
use App\Models\Resident;
use Carbon\CarbonInterface;

class WorldProductionProcessor
{
    /**
     * 건물 코드 → 기본 생산 레시피 코드. 집/창고/마왕성은 생산이 없다.
     */
    private const RECIPE_BY_BUILDING = [
        'farm' => 'farm_food',
        'lumberyard' => 'lumberyard_wood',
        'mine' => 'mine_ore',
        'market' => 'market_trade',
        'blacksmith' => 'blacksmith_weapon',
        'research_lab' => 'research_lab_mana',
        'tavern' => 'tavern_income',
    ];

    /**
     * README 7.2 방식: 마지막 처리 시각 이후 경과 시간을 계산해
     * 건설 완료와 생산 누적을 한 번에 반영한다. 호출 측에서 월드 행을 잠근 트랜잭션
     * 안에서 호출해야 한다.
     */
    public function process(GameWorld $world): void
    {
        $now = now();

        $buildings = $world->buildings()
            ->with([
                'buildingType',
                'productions' => fn ($query) => $query->where('is_active', true),
                'assignedResidents.residentType',
            ])
            ->get();

        foreach ($buildings as $building) {
            if ($building->state === 'constructing' && $building->finishes_at !== null && $building->finishes_at->lte($now)) {
                $this->completeConstruction($building, $now);
            }

            if ($building->state === 'active') {
                $this->accrueProduction($world, $building, $now);
            }
        }

        $world->last_active_at = $now;
        $world->save();
    }

    private function completeConstruction(Building $building, CarbonInterface $now): void
    {
        $building->update(['state' => 'active', 'last_processed_at' => $now]);

        $recipeCode = self::RECIPE_BY_BUILDING[$building->buildingType->code] ?? null;
        if ($recipeCode === null) {
            return;
        }

        $recipe = ProductionRecipe::where('code', $recipeCode)->first();
        if ($recipe === null) {
            return;
        }

        BuildingProduction::create([
            'building_id' => $building->id,
            'recipe_id' => $recipe->id,
            'stored_amount' => 0,
            'started_at' => $now,
            'last_processed_at' => $now,
            'next_completion_at' => $now->copy()->addSeconds($recipe->duration_seconds),
            'is_active' => true,
        ]);
    }

    private function accrueProduction(GameWorld $world, Building $building, CarbonInterface $now): void
    {
        $production = $building->productions->first();
        if ($production === null) {
            return;
        }

        $recipe = $production->recipe;
        $elapsedSeconds = $production->last_processed_at->diffInSeconds($now);
        $cycles = intdiv($elapsedSeconds, $recipe->duration_seconds);

        if ($cycles <= 0) {
            return;
        }

        $outputPerCycle = $this->outputPerCycle($building, $recipe);

        $levelDefinition = BuildingLevelDefinition::where('building_type_id', $building->building_type_id)
            ->where('level', $building->level)
            ->first();
        $storageCapacity = $levelDefinition?->storage_capacity;

        if ($storageCapacity !== null) {
            $room = max(0, $storageCapacity - $production->stored_amount);
            $cycles = min($cycles, intdiv($room, $outputPerCycle));
        }

        $inputResource = null;
        if ($recipe->input_resource_type !== null) {
            $inputResource = $world->resources()->where('resource_type', $recipe->input_resource_type)->first();
            $available = $inputResource->amount ?? 0;
            $cycles = min($cycles, intdiv($available, $recipe->input_amount));
        }

        if ($cycles <= 0) {
            return;
        }

        if ($inputResource !== null) {
            $inputResource->decrement('amount', $cycles * $recipe->input_amount);
        }

        $production->stored_amount += $cycles * $outputPerCycle;
        $production->last_processed_at = $production->last_processed_at->copy()->addSeconds($cycles * $recipe->duration_seconds);
        $production->next_completion_at = $production->last_processed_at->copy()->addSeconds($recipe->duration_seconds);
        $production->save();
    }

    /**
     * 배정된 주민의 생산 스탯만큼 사이클당 생산량을 늘린다(가산 % 보너스).
     */
    private function outputPerCycle(Building $building, ProductionRecipe $recipe): int
    {
        $bonusPercent = $building->assignedResidents->sum(
            fn (Resident $resident) => $resident->residentType->base_production,
        );

        return (int) round($recipe->output_amount * (1 + $bonusPercent / 100));
    }
}
