<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['game_world_id', 'resource_type', 'amount', 'capacity'])]
class WorldResource extends Model
{
    const CREATED_AT = null;

    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }
}
