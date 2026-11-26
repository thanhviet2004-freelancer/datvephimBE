<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $guarded = [];

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(Showtime::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }
}
