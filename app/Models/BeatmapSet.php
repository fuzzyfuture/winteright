<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BeatmapSet extends Model
{
    protected $fillable = [
        'set_id', 'creator_id', 'date_ranked', 'genre', 'lang',
        'artist', 'title', 'has_storyboard', 'has_video'
    ];

    protected $casts = [
        'date_ranked' => 'datetime',
    ];

    public function beatmaps(): BeatmapSet|HasMany
    {
        return $this->hasMany(Beatmap::class, 'set_id', 'set_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id', 'osu_id');
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = $this->beatmaps->pluck('status')->unique()->sort();

        if ($statuses->count() === 1) {
            return Beatmap::statusLabel($statuses->first());
        }

        return 'Mixed';
    }

    public function getGenreLabelAttribute(): string
    {
        return match ($this->genre) {
            2 => 'Video Game',
            3 => 'Anime',
            4 => 'Rock',
            5 => 'Pop',
            6 => 'Other Genre',
            7 => 'Novelty',
            9 => 'Hip Hop',
            10 => 'Electronic',
            11 => 'Metal',
            12 => 'Classical',
            13 => 'Folk',
            14 => 'Jazz',
            default => 'Unknown',
        };
    }

    public function getLanguageLabelAttribute(): string
    {
        return match ($this->lang) {
            2 => 'English',
            3 => 'Japanese',
            4 => 'Chinese',
            5 => 'Instrumental',
            6 => 'Korean',
            7 => 'French',
            8 => 'German',
            9 => 'Swedish',
            10 => 'Spanish',
            11 => 'Italian',
            12 => 'Russian',
            13 => 'Polish',
            14 => 'Other Language',
            default => 'Unknown',
        };
    }

}
