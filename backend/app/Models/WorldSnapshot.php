<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['game_world_id', 'revision', 'snapshot_type', 'state_json', 'checksum'])]
class WorldSnapshot extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'state_json' => 'array',
        ];
    }

    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }
}
