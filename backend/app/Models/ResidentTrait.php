<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['resident_id', 'trait_type', 'value'])]
class ResidentTrait extends Model
{
    public $timestamps = false;

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }
}
