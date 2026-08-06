<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'building_type_id',
    'level',
    'build_cost',
    'production_time',
    'production_amount',
    'storage_capacity',
    'worker_capacity',
])]
class BuildingLevelDefinition extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'build_cost' => 'array',
        ];
    }

    public function buildingType(): BelongsTo
    {
        return $this->belongsTo(BuildingDefinition::class, 'building_type_id');
    }
}
