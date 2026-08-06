<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'width', 'height', 'max_level', 'base_build_time', 'is_active'])]
class BuildingDefinition extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function levels(): HasMany
    {
        return $this->hasMany(BuildingLevelDefinition::class, 'building_type_id');
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class, 'building_type_id');
    }
}
