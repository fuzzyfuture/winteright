<?php

namespace App\Models;

use App\Enums\BeatmapMode;
use App\Helpers\OsuUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

class BeatmapSet extends Model
{
    protected $fillable = [
        'id', 'creator_id', 'date_ranked', 'genre', 'lang',
        'artist', 'title', 'has_storyboard', 'has_video',
    ];

    protected $casts = [
        'date_ranked' => 'datetime',
    ];

    public function beatmaps(): BeatmapSet|HasMany
    {
        return $this->hasMany(Beatmap::class, 'set_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function creatorName(): BelongsTo
    {
        return $this->belongsTo(BeatmapCreatorName::class, 'creator_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'beatmap_set_id', 'id');
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = $this->beatmaps->pluck('status')->unique();

        if ($statuses->count() === 1) {
            return Beatmap::getStatusLabel($statuses->first());
        }

        return 'mixed';
    }

    public function getGenreLabelAttribute(): string
    {
        return match ($this->genre) {
            2 => 'video game',
            3 => 'anime',
            4 => 'rock',
            5 => 'pop',
            6 => 'other genre',
            7 => 'novelty',
            9 => 'hip hop',
            10 => 'electronic',
            11 => 'metal',
            12 => 'classical',
            13 => 'folk',
            14 => 'jazz',
            default => 'unknown',
        };
    }

    public function getLanguageLabelAttribute(): string
    {
        return match ($this->lang) {
            2 => 'english',
            3 => 'japanese',
            4 => 'chinese',
            5 => 'instrumental',
            6 => 'korean',
            7 => 'french',
            8 => 'german',
            9 => 'swedish',
            10 => 'spanish',
            11 => 'italian',
            12 => 'russian',
            13 => 'polish',
            14 => 'other language',
            default => 'unknown',
        };
    }

    public function getInfoUrlAttribute(): string
    {
        return OsuUrl::beatmapSetInfo($this->id);
    }

    public function getBgUrlAttribute(): string
    {
        return OsuUrl::beatmapCover($this->id);
    }

    public function getPreviewUrlAttribute(): string
    {
        return OsuUrl::beatmapPreview($this->id);
    }

    public function getDirectUrlAttribute(): string
    {
        return OsuUrl::beatmapDirect($this->beatmaps->first()->id);
    }

    public function getCreatorProfileUrlAttribute(): string
    {
        return OsuUrl::userProfile($this->creator_id);
    }

    public function getCreatorLabelAttribute(): HtmlString
    {
        if ($this->creator) {
            $localLink = '<a href="'.route('users.show', $this->creator_id).'">'.e($this->creator->name).'</a>';
        } elseif ($this->creatorName) {
            $localLink = e($this->creatorName->name);
        } else {
            $localLink = $this->creator_id;
        }

        $extLink = '<a href="'.$this->creator_profile_url.'"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="view on osu!"
                    class="opacity-50 small">
                        <i class="bi bi-box-arrow-up-right"></i>
                </a>';

        return new HtmlString($localLink.$extLink);
    }

    public function getLinkAttribute(): HtmlString
    {
        $localUrl = route('beatmaps.show', $this->id);
        $text = $this->artist.' - '.$this->title;

        $localLink = '<a href="'.$localUrl.'">'.e($text).'</a>';
        $extLink = '<a href="'.$this->info_url.'"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="view on osu!"
                       class="opacity-50 small">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>';

        return new HtmlString($localLink.$extLink);
    }

    public function getDifficultySpreadAttribute(): HtmlString
    {
        $modeCounts = $this->beatmaps->groupBy('mode')->map->count();
        $output = $modeCounts->map(function ($count, $mode) {
            $icon = Beatmap::getModeIcon(BeatmapMode::from($mode));

            return '<span class="mode-badge d-flex align-items-center">'.$icon.'<span class="count">'.$count.'</span></span>';
        })->implode(' ');

        return new HtmlString('<div class="d-flex align-items-center gap-2">'.$output.'</div>');
    }

    public function getStatusBadgeAttribute(): HtmlString
    {
        $output = '<span class="badge text-bg-primary">'.$this->status_label.'</span>';

        return new HtmlString($output);
    }
}
