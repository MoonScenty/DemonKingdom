<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'game_world_id',
    'building_type_id',
    'x',
    'y',
    'rotation',
    'level',
    'state',
    'started_at',
    'finishes_at',
    'last_processed_at',
])]
class Building extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finishes_at' => 'datetime',
            'last_processed_at' => 'datetime',
        ];
    }

    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }

    public function buildingType(): BelongsTo
    {
        return $this->belongsTo(BuildingDefinition::class, 'building_type_id');
    }

    public function productions(): HasMany
    {
        return $this->hasMany(BuildingProduction::class);
    }

    public function assignedResidents(): HasMany
    {
        return $this->hasMany(Resident::class, 'assigned_building_id');
    }
}
