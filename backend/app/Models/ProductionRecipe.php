<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'input_resource_type',
    'input_amount',
    'output_resource_type',
    'output_amount',
    'duration_seconds',
])]
class ProductionRecipe extends Model
{
    public $timestamps = false;

    public function productions(): HasMany
    {
        return $this->hasMany(BuildingProduction::class, 'recipe_id');
    }
}
