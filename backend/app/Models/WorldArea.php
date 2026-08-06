<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['game_world_id', 'area_type', 'is_unlocked', 'unlocked_at'])]
class WorldArea extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_unlocked' => 'boolean',
            'unlocked_at' => 'datetime',
        ];
    }

    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }

    public function tiles(): HasMany
    {
        return $this->hasMany(WorldTile::class, 'area_id');
    }
}
