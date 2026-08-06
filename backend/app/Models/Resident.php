<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'game_world_id',
    'resident_type_id',
    'name',
    'level',
    'experience',
    'loyalty',
    'health_state',
    'current_state',
    'assigned_building_id',
])]
class Resident extends Model
{
    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }

    public function residentType(): BelongsTo
    {
        return $this->belongsTo(ResidentDefinition::class, 'resident_type_id');
    }

    public function assignedBuilding(): BelongsTo
    {
        return $this->belongsTo(Building::class, 'assigned_building_id');
    }

    public function traits(): HasMany
    {
        return $this->hasMany(ResidentTrait::class);
    }
}
