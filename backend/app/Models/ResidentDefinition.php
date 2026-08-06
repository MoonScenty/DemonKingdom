<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'race',
    'base_production',
    'base_construction',
    'base_research',
    'base_combat',
    'base_movement',
    'base_charm',
])]
class ResidentDefinition extends Model
{
    public $timestamps = false;

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'resident_type_id');
    }
}
