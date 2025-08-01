<?php

namespace App\Models;

use App\Services\BeatmapService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

class BeatmapSet extends Model
{
    protected $fillable = [
        'id', 'creator_id', 'date_ranked', 'genre', 'lang',
        'artist', 'title', 'has_storyboard', 'has_video'
    ];

    protected $casts = [
        'date_ranked' => 'datetime',
    ];

    protected array $externalCreatorLabel = [];

    public function beatmaps(): BeatmapSet|HasMany
    {
        return $this->hasMany(Beatmap::class, 'set_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = $this->beatmaps->pluck('status')->unique()->sort();

        if ($statuses->count() === 1) {
            return Beatmap::statusLabel($statuses->first());
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

    public function setExternalCreatorLabel(array $label): void
    {
        $this->externalCreatorLabel = $label;
    }

    public function getCreatorLabelAttribute(): HtmlString
    {
        $label = $this->externalCreatorLabel;

        if (empty($label)) {
            $id = $this->creator_id;

            if ($this->creator) {
                $name = e($this->creator->name);
                $isWinteright = true;
            } else {
                $beatmapService = app(BeatmapService::class);
                $name = $beatmapService->getCreatorName($this->creator_id);
                $isWinteright = false;
            }

            $label = [
              'id' => $id,
              'name' => $name,
              'isWinteright' => $isWinteright,
            ];
        }

        if ($label['isWinteright']) {
            $url = url('/users/'.$label['id']);
            $name = e($label['name']);
            $localLink = '<a href="'.$url.'">'.$name.'</a>';
        } else if (!blank($label['name'])) {
            $localLink = e($label['name']);
        } else {
            $localLink = e($label['id']);
        }

        $extLink = '<a href="https://osu.ppy.sh/users/'.$label['id'].'"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="view on osu!"
                       class="opacity-50 small">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>';

        return new HtmlString($localLink.$extLink);
    }
}
