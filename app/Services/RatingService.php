<?php

namespace App\Services;

use App\Models\Rating;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class RatingService
{
    /**
     * Sets a rating.
     * @param int $userId The user ID to set a rating for.
     * @param int $beatmapId The beatmap ID to set a rating for.
     * @param int $score The rating score to set.
     * @return void
     * @throws Throwable
     */
    public function set(int $userId, int $beatmapId, int $score): void
    {
        DB::transaction(function () use ($userId, $beatmapId, $score) {
            Rating::updateOrCreate(
                [
                    'user_id' => $userId,
                    'beatmap_id' => $beatmapId,
                ],
                [
                    'score' => $score,
                ]
            );
        });

        $beatmapService = app(BeatmapService::class);
        $beatmapService->updateWeightedAverage($beatmapId);
    }

    /**
     * Deletes a rating.
     * @param int $userId The user ID of the rating to be deleted.
     * @param int $beatmapId The beatmap ID of the rating to be deleted.
     * @return void
     * @throws Throwable
     */
    public function clear(int $userId, int $beatmapId): void
    {
        DB::transaction(function () use ($userId, $beatmapId) {
            Rating::where('user_id', $userId)
                ->where('beatmap_id', $beatmapId)
                ->delete();
        });

        $beatmapService = app(BeatmapService::class);
        $beatmapService->updateWeightedAverage($beatmapId);
    }

    /**
     * Retrieves recent ratings.
     * @param int $limit The amount of recent ratings to retrieve. Defaults to 20.
     * @return Collection The recent ratings.
     */
    public function getRecent(int $limit = 15): Collection
    {
        return Cache::remember('recent_'.$limit.'_ratings', 30, function () use ($limit) {
            return Rating::orderByDesc('updated_at')
                ->with('user')
                ->with('beatmap.set')
                ->whereRelation('beatmap', 'blacklisted', false)
                ->limit($limit)
                ->get();
        });
    }
}
