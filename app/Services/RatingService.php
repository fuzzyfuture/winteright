<?php

namespace App\Services;

use App\Models\Rating;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RatingService
{
    /**
     * Sets a rating.
     * @param int $userId The user ID to set a rating for.
     * @param int $beatmapId The beatmap ID to set a rating for.
     * @param int $score The rating score to set.
     * @return void
     */
    public function set(int $userId, int $beatmapId, int $score): void
    {
        Rating::updateOrCreate(
            [
                'user_id' => $userId,
                'beatmap_id' => $beatmapId,
            ],
            [
                'score' => $score,
            ]
        );
    }

    /**
     * Deletes a rating.
     * @param int $userId The user ID of the rating to be deleted.
     * @param int $beatmapId The beatmap ID of the rating to be deleted.
     * @return void
     */
    public function clear(int $userId, int $beatmapId): void
    {
        Rating::where('user_id', $userId)
            ->where('beatmap_id', $beatmapId)
            ->delete();
    }

    /**
     * Retrieves recent ratings.
     * @param int $limit The amount of recent ratings to retrieve. Defaults to 20.
     * @return Collection The recent ratings.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return Cache::remember('recent_'.$limit.'_ratings', 30, function () use ($limit) {
            return Rating::orderByDesc('updated_at')
                ->with('user')
                ->with('beatmap.set')
                ->limit($limit)
                ->get();
        });
    }
}
