<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['game_world_id', 'quest_id', 'status', 'progress', 'completed_at', 'rewarded_at'])]
class WorldQuest extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'rewarded_at' => 'datetime',
        ];
    }

    public function gameWorld(): BelongsTo
    {
        return $this->belongsTo(GameWorld::class);
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}
