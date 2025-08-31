<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

class Beatmap extends Model
{
    protected $fillable = [
        'id', 'set_id', 'difficulty_name', 'mode', 'status', 'sr',
        'weighted_avg', 'bayesian_avg',
        'blacklisted', 'blacklist_reason',
    ];

    protected $casts = [
        'mode' => BeatmapMode::class,
    ];

    protected array $externalCreatorLabels = [];

    public function set(): BelongsTo
    {
        return $this->belongsTo(BeatmapSet::class, 'set_id', 'id');
    }

    public function creators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'beatmap_creators', 'beatmap_id', 'creator_id', 'id', 'id');
    }

    public function ratings(): HasMany|Beatmap
    {
        return $this->hasMany(Rating::class);
    }

    public function userRating()
    {
        return $this->hasOne(Rating::class)->where('user_id', Auth::id());
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            -2 => 'graveyard',
            1 => 'ranked',
            2 => 'approved',
            3 => 'qualified',
            4 => 'loved',
            default => 'unknown',
        };
    }

    public function getDateLabelAttribute(): string
    {
        return match ($this->status) {
            1, 2 => 'ranked',
            3 => 'qualified',
            4 => 'loved',
            default => 'submitted',
        };
    }

    public function getModeIconAttribute(): HtmlString
    {
        $fileName = match ($this->mode) {
            0 => 'mode-osu-small',
            1 => 'mode-taiko-small',
            2 => 'mode-fruits-small',
            3 => 'mode-mania-small',
        };

        return new HtmlString('<img src="'.asset('/img/modes/'.$fileName.'.png').'"/>');
    }

    public function setExternalCreatorLabels(array $labels): void
    {
        $this->externalCreatorLabels = $labels;
    }

    public function getCreatorLabelAttribute(): HtmlString
    {
        $labels = $this->externalCreatorLabels;

        if (empty($labels)) {
            return $this->set?->creator?->url ?? new HtmlString('unknown');
        }

        $output = '';
        $chunks = [];

        foreach ($labels as $creator) {
            if ($creator['isWinteright']) {
                $localLink = '<a href="'.route('users.show', $creator['id']).'">'.e($creator['name']).'</a>';
            } else if (!empty($creator['name'])) {
                $localLink = e($creator['name']);
            } else {
                $localLink = e($creator['id']);
            }

            $extLink = '<a href="https://osu.ppy.sh/users/'.$creator['id'].'"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="view on osu!"
                   class="opacity-50 small">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>';

            $chunks[] = $localLink.$extLink;
        }

        $output .= implode(', ', $chunks);
        return new HtmlString($output);
    }

    public function getUrlAttribute(): HtmlString
    {
        $localUrl = route('beatmaps.show', $this->set_id);
        $text = $this->set->artist.' - '.$this->set->title.' ['.$this->difficulty_name.']';

        $localLink = '<a href="'.$localUrl.'">'.e($text).'</a>';
        $extLink = '<a href="https://osu.ppy.sh/beatmapsets/'.$this->set_id.'#osu/'.$this->id.'"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="view on osu!"
                       class="opacity-50 small">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>';

        return new HtmlString($localLink.$extLink);
    }

    public function getBgUrlAttribute(): string
    {
        return 'https://assets.ppy.sh/beatmaps/'.$this->set_id.'/covers/cover.jpg';
    }
}
