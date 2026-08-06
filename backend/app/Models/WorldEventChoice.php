<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['world_event_id', 'choice_code', 'selected_at', 'result_payload'])]
class WorldEventChoice extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'selected_at' => 'datetime',
            'result_payload' => 'array',
        ];
    }

    public function worldEvent(): BelongsTo
    {
        return $this->belongsTo(WorldEvent::class);
    }
}
