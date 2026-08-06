<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'game_world_id',
    'user_id',
    'action_type',
    'target_type',
    'target_id',
    'before_payload',
    'after_payload',
    'ip_address',
])]
class GameActionLog extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'before_payload' => 'array',
            'after_payload' => 'array',
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
