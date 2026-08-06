<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'type', 'name', 'description', 'reward', 'is_active'])]
class Quest extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'reward' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function worldQuests(): HasMany
    {
        return $this->hasMany(WorldQuest::class);
    }
}
