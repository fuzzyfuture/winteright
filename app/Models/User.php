<?php

namespace App\Models;

use App\Enums\HideCommentsOption;
use App\Enums\HideRatingsOption;
use App\Helpers\OsuUrl;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\HtmlString;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['id', 'name', 'banned', 'bio', 'title', 'enabled_modes', 'hide_ratings', 'hide_comments',
        'osu_access_token', 'osu_refresh_token', 'osu_token_expires_at'];
    protected $casts = [
        'hide_ratings' => HideRatingsOption::class,
        'hide_comments' => HideCommentsOption::class,
    ];

    public $incrementing = false;

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function favoriteLists(): BelongsToMany
    {
        return $this->belongsToMany(UserList::class, 'user_list_favorites', 'user_id', 'list_id');
    }

    public function beatmapSets(): HasMany
    {
        return $this->hasMany(BeatmapSet::class, 'creator_id');
    }

    public function guestDifficulties(): BelongsToMany
    {
        return $this->belongsToMany(Beatmap::class, 'beatmap_creators', 'creator_id', 'beatmap_id')
            ->whereHas('set', function ($query) {
                $query->where('creator_id', '!=', $this->id);
            });
    }

    public function hasFavorited($listId): bool
    {
        return $this->favoriteLists()->where('list_id', $listId)->exists();
    }

    public function hasModeEnabled($mode): bool
    {
        return (bool) ($this->enabled_modes & (1 << $mode->value));
    }

    public function getProfileUrlAttribute(): string
    {
        return OsuUrl::userProfile($this->id);
    }

    public function getAvatarUrlAttribute(): string
    {
        return OsuUrl::userAvatar($this->id);
    }

    public function getLinkAttribute(): HtmlString
    {
        $localUrl = route('users.show', $this->id);
        $localLink = '<a href="'.$localUrl.'">'.$this->name.'</a>';
        $extLink = '<a href="'.$this->profile_url.'"
               target="_blank"
               rel="noopener noreferrer"
               title="view on osu!"
               class="opacity-50 small">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>';

        return new HtmlString($localLink.$extLink);
    }

    public function isAdmin(): bool
    {
        return $this->id === 2966685;
    }
}
