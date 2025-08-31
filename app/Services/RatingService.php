<?php

namespace App\Services;

use App\Enums\BeatmapMode;
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
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $limit The amount of recent ratings to retrieve. Defaults to 20.
     * @return Collection The recent ratings.
     */
    public function getRecent(int $enabledModes, int $limit = 15): Collection
    {
        return Cache::remember('recent_'.$limit.'_ratings_'.$enabledModes, 30, function () use ($enabledModes, $limit) {
            $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

            return Rating::orderByDesc('updated_at')
                ->with('user')
                ->with('beatmap.set')
                ->whereHas('beatmap', function ($query) use ($modesArray) {
                    $query->whereIn('mode', $modesArray)
                        ->where('blacklisted', false);
                })
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Retrieves all ratings for a given list of beatmap IDs.
     *
     * @param Collection $ids The list of beatmap IDs.
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $perPage The amount of ratings to display per page.
     * @return Paginator The paginated ratings.
     */
    public function getForBeatmaps(Collection $ids, int $enabledModes, int $perPage = 15): Paginator
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return Rating::orderByDesc('updated_at')
            ->with('user')
            ->with('beatmap.set')
            ->whereIn('beatmap_id', $ids)
            ->whereHas('beatmap', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray)
                    ->where('blacklisted', false);
            })
            ->simplePaginate($perPage);
    }

    /**
     * Retrieves all ratings for a given user.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $userId The user's ID.
     * @param int $perPage The amount of ratings to display per page.
     * @return LengthAwarePaginator The paginated ratings.
     */
    public function getForUser(int $enabledModes, int $userId, ?float $score, int $perPage = 50): LengthAwarePaginator
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);
        $query = Rating::orderByDesc('updated_at')
            ->with('beatmap.set')
            ->where('user_id', $userId)
            ->whereHas('beatmap', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray)
                    ->where('blacklisted', false);
            });

        if (!is_null($score)) {
            $query->whereRaw('score / 2 = '.$score);
        }

        return $query->paginate($perPage);
    }

    /**
     * Retrieves recent ratings for a specified user.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $userId The user's ID.
     * @param int $limit The amount of recent ratings to retrieve.
     * @return Collection The user's recent ratings.
     */
    public function getRecentForUser(int $enabledModes, int $userId, int $limit = 5): Collection
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return Rating::where('user_id', $userId)
            ->with('user')
            ->with('beatmap.set')
            ->whereHas('beatmap', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray);
            })
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieves the users rating spread as an array of the rating bin (e.g. 0.5, 1.0) and the amount of ratings
     * in that bin.
     * @param int $userId The user's ID.
     * @return Collection The user's rating spread.
     */
    public function getSpreadForUser(int $userId): Collection
    {
        return Rating::where('user_id', $userId)
            ->selectRaw('score as rating_bin, COUNT(*) as count')
            ->groupBy('rating_bin')
            ->orderBy('rating_bin')
            ->pluck('count', 'rating_bin');
    }
}
