<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'game_world_id',
    'user_id',
    'command_id',
    'command_type',
    'base_revision',
    'status',
    'request_payload',
    'response_payload',
    'completed_at',
])]
class GameCommand extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response_payload' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
