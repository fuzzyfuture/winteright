<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

class BeatmapCreator extends Model
{
    protected $fillable = ['beatmap_id', 'creator_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function creatorName(): BelongsTo
    {
        return $this->belongsTo(BeatmapCreatorName::class, 'creator_id');
    }

    public function getUrlAttribute(): HtmlString
    {
        if ($this->user) {
            $localLink = '<a href="'.route('users.show', $this->creator_id).'">'.e($this->user->name).'</a>';
        } else if ($this->creatorName) {
            $localLink = e($this->creatorName->name);
        } else {
            $localLink = $this->creator_id;
        }

        $extLink = '<a href="https://osu.ppy.sh/users/'.$this->creator_id.'"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="view on osu!"
                    class="opacity-50 small">
                        <i class="bi bi-box-arrow-up-right"></i>
                </a>';

        return new HtmlString($localLink.$extLink);
    }
}
