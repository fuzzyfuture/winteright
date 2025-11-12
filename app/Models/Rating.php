<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = ['user_id', 'beatmap_id', 'score'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function beatmap(): BelongsTo
    {
        return $this->belongsTo(Beatmap::class);
    }

    public function getScoreDisplayAttribute(): float
    {
        return $this->score / 2;
    }
}
