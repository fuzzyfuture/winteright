<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    /**
     * Checks if a user with the given ID exists. Returns true if the user exists.
     * @param int $id The user ID to check.
     * @return bool True if the user exists.
     */
    public function exists(int $id): bool
    {
        return User::whereId($id)->exists();
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
