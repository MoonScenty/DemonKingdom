<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['game_world_id', 'area_id', 'x', 'y', 'terrain_type', 'is_buildable'])]
class WorldTile extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_buildable' => 'boolean',
        ];
    }

    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(WorldArea::class, 'area_id');
    }
}
