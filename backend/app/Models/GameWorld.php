<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'guest_session_id',
    'name',
    'city_level',
    'population',
    'current_era',
    'revision',
    'last_processed_at',
    'last_active_at',
])]
class GameWorld extends Model
{
    protected function casts(): array
    {
        return [
            'last_processed_at' => 'datetime',
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guestSession(): BelongsTo
    {
        return $this->belongsTo(GuestSession::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(WorldResource::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(WorldArea::class);
    }

    public function tiles(): HasMany
    {
        return $this->hasMany(WorldTile::class);
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function quests(): HasMany
    {
        return $this->hasMany(WorldQuest::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorldEvent::class);
    }

    public function research(): HasMany
    {
        return $this->hasMany(WorldResearch::class);
    }

    public function expeditions(): HasMany
    {
        return $this->hasMany(Expedition::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(GameCommand::class);
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(GameActionLog::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(WorldSnapshot::class);
    }
}
