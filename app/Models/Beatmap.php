<?php

namespace App\Models;

use App\Enums\BeatmapMode;
use App\Models\BeatmapCreator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
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

    public function set(): BelongsTo
    {
        return $this->belongsTo(BeatmapSet::class, 'set_id', 'id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function userRating(): HasOne
    {
        return $this->hasOne(Rating::class)->where('user_id', Auth::id());
    }

    public function creators(): HasMany
    {
        return $this->hasMany(BeatmapCreator::class, 'beatmap_id');
    }

    public static function getStatusLabel(int $status): string
    {
        return match ($status) {
            -2 => 'graveyard',
            1 => 'ranked',
            2 => 'approved',
            3 => 'qualified',
            4 => 'loved',
            default => 'unknown',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusLabel($this->status);
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

    public static function getModeIcon(BeatmapMode $mode): HtmlString
    {
        $fileName = match ($mode) {
            BeatmapMode::OSU => 'mode-osu-small',
            BeatmapMode::TAIKO => 'mode-taiko-small',
            BeatmapMode::FRUITS => 'mode-fruits-small',
            BeatmapMode::MANIA => 'mode-mania-small',
        };

        return new HtmlString('<img src="'.asset('/img/modes/'.$fileName.'.png').'"/>');
    }

    public function getModeIconAttribute(): HtmlString
    {
        return self::getModeIcon($this->mode);
    }

    public function getCreatorLabelAttribute(): HtmlString
    {
        if ($this->creators->isEmpty()) {
            return $this->set->creator_label;
        }

        $urls = $this->creators->map(function ($creator) {
            return $creator->url->toHtml();
        });

        return new HtmlString($urls->implode(', '));
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

    public function getPreviewUrlAttribute(): string
    {
        return 'https://b.ppy.sh/preview/'.$this->set_id.'.mp3';
    }

    public function getStatusBadgeAttribute(): HtmlString
    {
        $output = '<span class="badge text-bg-primary">'.$this->status_label.'</span>';

        return new HtmlString($output);
    }
}
