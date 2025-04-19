<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Beatmap extends Model
{
    protected $fillable = [
        'beatmap_id', 'set_id', 'difficulty_name', 'mode', 'status', 'sr',
        'rating', 'chart_rank', 'chart_year_rank', 'rating_count', 'weighted_avg', 'bayesian_avg',
        'blacklisted', 'blacklist_reason', 'controversy'
    ];

    public function set(): BelongsTo
    {
        return $this->belongsTo(BeatmapSet::class, 'set_id', 'set_id');
    }

    public function creators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'beatmap_creators', 'beatmap_id', 'creator_id', 'beatmap_id', 'osu_id');
    }

    public function ratings(): HasMany|Beatmap
    {
        return $this->hasMany(Rating::class);
    }

    public function userRating()
    {
        return $this->hasOne(Rating::class)->where('user_id', auth()->id());
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            -2 => 'Graveyard',
            1 => 'Ranked',
            2 => 'Approved',
            3 => 'Qualified',
            4 => 'Loved',
            default => 'unknown',
        };
    }

    public function getDateLabelAttribute(): string
    {
        return match ($this->status) {
            1, 2 => 'Ranked',
            3 => 'Qualified',
            4 => 'Loved',
            default => 'Submitted',
        };
    }
}
