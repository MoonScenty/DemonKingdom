<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BuildingDefinition;
use Illuminate\Http\JsonResponse;

class GameConfigController extends Controller
{
    public function index(): JsonResponse
    {
        $buildings = BuildingDefinition::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->with(['levels' => fn ($query) => $query->where('level', 1)])
            ->get()
            ->map(function (BuildingDefinition $definition) {
                $levelOne = $definition->levels->first();

                return [
                    'code' => $definition->code,
                    'name' => $definition->name,
                    'width' => $definition->width,
                    'height' => $definition->height,
                    'baseBuildTime' => $definition->base_build_time,
                    'buildCost' => $levelOne->build_cost ?? [],
                ];
            })
            ->values();

        return response()->json(['buildings' => $buildings]);
    }
}
