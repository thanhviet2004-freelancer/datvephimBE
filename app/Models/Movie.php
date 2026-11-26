<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $guarded = [];

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class);
    }
}
