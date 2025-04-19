<?php

namespace App\Services;

use App\Models\Rating;
use Illuminate\Support\Facades\Auth;

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
}
