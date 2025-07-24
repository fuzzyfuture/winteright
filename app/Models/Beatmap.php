<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class Beatmap extends Model
{
    protected $fillable = [
        'id', 'set_id', 'difficulty_name', 'mode', 'status', 'sr',
        'weighted_avg', 'bayesian_avg',
        'blacklisted', 'blacklist_reason',
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

    public function setExternalCreatorLabels(array $labels): void
    {
        $this->externalCreatorLabels = $labels;
    }

    public function getCreatorLabelAttribute(): HtmlString
    {
        $labels = $this->externalCreatorLabels;

        if (empty($labels) && $this->set?->creator) {
            $url = url('/users/' . $this->set->creator_id);
            return new HtmlString('<a href="'.$url.'">'.e($this->set->creator->name).'</a>'.
                '<a href="https://osu.ppy.sh/users/'.$this->set->creator_id.'"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="view on osu!"
                   class="opacity-50 small">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>');
        }

        if (empty($labels)) {
            return new HtmlString('unknown');
        }

        $output = '';
        $chunks = [];

        foreach ($labels as $creator) {
            if (!empty($creator['name'])) {
                $localLink = '<a href="'.url('/users/'.$creator['id']).'">'.e($creator['name']).'</a>';
            } else {
                $localLink = e($creator['id']);
            }

            $chunks[] = $localLink.
                '<a href="https://osu.ppy.sh/users/'.$creator['id'].'"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="view on osu!"
                   class="opacity-50 small">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>';
        }

        $output .= implode(', ', $chunks);
        return new HtmlString($output);
    }
}
