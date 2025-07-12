<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    protected BeatmapService $beatmapService;

    public function __construct(BeatmapService $beatmapService)
    {
        $this->beatmapService = $beatmapService;
    }

    /**
     * Retrieves a user by their osu! ID.
     * @param int $osuId The user's osu! ID.
     * @return User The user object.
     */
    public function getByOsuId(int $osuId): User
    {
        return User::where('osu_id', $osuId)->firstOrFail();
    }

    /**
     * Retrieves user data for the profile page. Includes their 5 latest ratings and an array of counts for each
     * rating value.
     * @param User $user The user.
     * @return array The user's data for the profile page.
     */
    public function getProfileData(User $user): array
    {
        $recentRatings = $user->ratings()
            ->with('beatmap.set')
            ->latest('updated_at')
            ->take(5)
            ->get();

        $this->beatmapService->applyCreatorLabels($recentRatings->pluck('beatmap'));

        $ratingSpread = $user->ratings()
            ->selectRaw('score as rating_bin, COUNT(*) as count')
            ->groupBy('rating_bin')
            ->orderBy('rating_bin')
            ->pluck('count', 'rating_bin');

        return compact('user', 'ratingSpread', 'recentRatings');
    }
}
