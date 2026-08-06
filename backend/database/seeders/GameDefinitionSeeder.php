<?php

namespace Database\Seeders;

use App\Models\BuildingDefinition;
use App\Models\BuildingLevelDefinition;
use App\Models\ProductionRecipe;
use App\Models\ResidentDefinition;
use Illuminate\Database\Seeder;

class GameDefinitionSeeder extends Seeder
{
    /**
     * README 17장 MVP 범위(건물 10종, 주민 3종, 자원 5종) 기준 정의 데이터를 채운다.
     * 레벨 2 이상 성장 단계는 아직 밸런스가 정해지지 않아 레벨 1만 시딩한다.
     */
    public function run(): void
    {
        $this->seedBuildings();
        $this->seedProductionRecipes();
        $this->seedResidents();
    }

    private function seedBuildings(): void
    {
        $buildings = [
            [
                'code' => 'castle',
                'name' => '마왕성',
                'build_time' => 0,
                'level' => [
                    'build_cost' => [],
                    'production_time' => null,
                    'production_amount' => null,
                    'storage_capacity' => 500,
                    'worker_capacity' => 2,
                ],
            ],
            [
                'code' => 'house',
                'name' => '주택',
                'build_time' => 60,
                'level' => [
                    'build_cost' => ['gold' => 100, 'wood' => 80],
                    'production_time' => null,
                    'production_amount' => null,
                    'storage_capacity' => null,
                    'worker_capacity' => 4,
                ],
            ],
            [
                'code' => 'farm',
                'name' => '농장',
                'build_time' => 45,
                'level' => [
                    'build_cost' => ['gold' => 80, 'wood' => 60],
                    'production_time' => 60,
                    'production_amount' => 10,
                    'storage_capacity' => 200,
                    'worker_capacity' => 2,
                ],
            ],
            [
                'code' => 'lumberyard',
                'name' => '벌목장',
                'build_time' => 45,
                'level' => [
                    'build_cost' => ['gold' => 80, 'food' => 40],
                    'production_time' => 60,
                    'production_amount' => 10,
                    'storage_capacity' => 200,
                    'worker_capacity' => 2,
                ],
            ],
            [
                'code' => 'mine',
                'name' => '광산',
                'build_time' => 60,
                'level' => [
                    'build_cost' => ['gold' => 100, 'wood' => 60],
                    'production_time' => 60,
                    'production_amount' => 8,
                    'storage_capacity' => 200,
                    'worker_capacity' => 3,
                ],
            ],
            [
                'code' => 'warehouse',
                'name' => '창고',
                'build_time' => 40,
                'level' => [
                    'build_cost' => ['gold' => 60, 'wood' => 80],
                    'production_time' => null,
                    'production_amount' => null,
                    'storage_capacity' => 1000,
                    'worker_capacity' => 0,
                ],
            ],
            [
                'code' => 'market',
                'name' => '시장',
                'build_time' => 50,
                'level' => [
                    'build_cost' => ['gold' => 120, 'wood' => 40],
                    'production_time' => 90,
                    'production_amount' => 15,
                    'storage_capacity' => 300,
                    'worker_capacity' => 2,
                ],
            ],
            [
                'code' => 'blacksmith',
                'name' => '대장간',
                'build_time' => 70,
                'level' => [
                    'build_cost' => ['gold' => 150, 'wood' => 60, 'ore' => 40],
                    'production_time' => 80,
                    'production_amount' => 6,
                    'storage_capacity' => 150,
                    'worker_capacity' => 2,
                ],
            ],
            [
                'code' => 'research_lab',
                'name' => '연구소',
                'build_time' => 80,
                'level' => [
                    'build_cost' => ['gold' => 150, 'mana' => 20],
                    'production_time' => 90,
                    'production_amount' => 5,
                    'storage_capacity' => 150,
                    'worker_capacity' => 2,
                ],
            ],
            [
                'code' => 'tavern',
                'name' => '주점',
                'build_time' => 55,
                'level' => [
                    'build_cost' => ['gold' => 100, 'food' => 60],
                    'production_time' => 70,
                    'production_amount' => 10,
                    'storage_capacity' => 200,
                    'worker_capacity' => 2,
                ],
            ],
        ];

        foreach ($buildings as $data) {
            $definition = BuildingDefinition::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'width' => 2,
                    'height' => 2,
                    'max_level' => 6,
                    'base_build_time' => $data['build_time'],
                    'is_active' => true,
                ],
            );

            BuildingLevelDefinition::updateOrCreate(
                ['building_type_id' => $definition->id, 'level' => 1],
                $data['level'],
            );
        }
    }

    private function seedProductionRecipes(): void
    {
        $recipes = [
            [
                'code' => 'farm_food',
                'input_resource_type' => null,
                'input_amount' => null,
                'output_resource_type' => 'food',
                'output_amount' => 10,
                'duration_seconds' => 60,
            ],
            [
                'code' => 'lumberyard_wood',
                'input_resource_type' => null,
                'input_amount' => null,
                'output_resource_type' => 'wood',
                'output_amount' => 10,
                'duration_seconds' => 60,
            ],
            [
                'code' => 'mine_ore',
                'input_resource_type' => null,
                'input_amount' => null,
                'output_resource_type' => 'ore',
                'output_amount' => 8,
                'duration_seconds' => 60,
            ],
            [
                'code' => 'market_trade',
                'input_resource_type' => null,
                'input_amount' => null,
                'output_resource_type' => 'gold',
                'output_amount' => 15,
                'duration_seconds' => 90,
            ],
            [
                'code' => 'blacksmith_weapon',
                'input_resource_type' => 'ore',
                'input_amount' => 4,
                'output_resource_type' => 'gold',
                'output_amount' => 12,
                'duration_seconds' => 80,
            ],
            [
                'code' => 'research_lab_mana',
                'input_resource_type' => null,
                'input_amount' => null,
                'output_resource_type' => 'mana',
                'output_amount' => 5,
                'duration_seconds' => 90,
            ],
            [
                'code' => 'tavern_income',
                'input_resource_type' => 'food',
                'input_amount' => 2,
                'output_resource_type' => 'gold',
                'output_amount' => 10,
                'duration_seconds' => 70,
            ],
        ];

        foreach ($recipes as $recipe) {
            ProductionRecipe::updateOrCreate(['code' => $recipe['code']], $recipe);
        }
    }

    private function seedResidents(): void
    {
        $residents = [
            [
                'code' => 'slime',
                'race' => 'slime',
                'base_production' => 20,
                'base_construction' => 10,
                'base_research' => 5,
                'base_combat' => 5,
                'base_movement' => 30,
                'base_charm' => 15,
            ],
            [
                'code' => 'goblin',
                'race' => 'goblin',
                'base_production' => 45,
                'base_construction' => 20,
                'base_research' => 15,
                'base_combat' => 20,
                'base_movement' => 30,
                'base_charm' => 20,
            ],
            [
                'code' => 'ogre',
                'race' => 'ogre',
                'base_production' => 15,
                'base_construction' => 50,
                'base_research' => 5,
                'base_combat' => 40,
                'base_movement' => 10,
                'base_charm' => 5,
            ],
        ];

        foreach ($residents as $resident) {
            ResidentDefinition::updateOrCreate(['code' => $resident['code']], $resident);
        }
    }
}
