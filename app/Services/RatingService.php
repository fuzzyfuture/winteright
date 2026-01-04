<?php

namespace App\Services;

use App\DataObjects\RatingGroup;
use App\Enums\BeatmapMode;
use App\Enums\HideRatingsOption;
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
     * Retrieves and groups recent ratings.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $limit The amount of recent ratings to retrieve. Defaults to 20.
     * @return Collection The recent ratings.
     */
    public function getRecent(int $enabledModes, int $limit = 15): Collection
    {
        return Cache::remember('ratings:recent:'.$limit.':'.$enabledModes, 120, function () use ($enabledModes, $limit) {
            $modesArray = BeatmapMode::bitfieldToArray($enabledModes);
            $ratings = Rating::orderByDesc('updated_at')
                ->with('user')
                ->with('beatmap.set')
                ->whereHas('beatmap', function ($query) use ($modesArray) {
                    $query->whereIn('mode', $modesArray)
                        ->where('blacklisted', false);
                })
                ->whereHas('user', function ($query) {
                    $query->where('hide_ratings', HideRatingsOption::NONE->value);
                })
                ->limit(1000)
                ->get();

            $grouped = collect();
            $currentGroup = null;

            foreach ($ratings as $rating) {
                if ($currentGroup && $currentGroup->user->id == $rating->user_id &&
                    $currentGroup->time->diffInMinutes($rating->updated_at) <= 5) {
                    $currentGroup->ratings->push($rating);
                    continue;
                }

                if ($grouped->count() == $limit) break;

                $currentGroup = new RatingGroup($rating->user, collect([$rating]), $rating->updated_at);
                $grouped->push($currentGroup);
            }

            return $grouped;
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
            ->whereHas('user', function ($query) {
                $query->where('hide_ratings', '!=', HideRatingsOption::ALL->value);
            })
            ->simplePaginate($perPage);
    }

    /**
     * Retrieves recent ratings for a specified user.
     *
     * @param int $userId The user's ID.
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $limit The amount of recent ratings to retrieve.
     * @return Collection The user's recent ratings.
     */
    public function getForUser(int $userId, int $enabledModes = 15, int $limit = 5): Collection
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return Rating::where('user_id', $userId)
            ->with(['user', 'beatmap.set', 'beatmap.creators.user', 'beatmap.creators.creatorName'])
            ->whereHas('beatmap', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray);
            })
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieves and paginates all ratings for a given user.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $userId The user's ID.
     * @param int $perPage The amount of ratings to display per page.
     * @return LengthAwarePaginator The paginated ratings.
     */
    public function getForUserPaginated(int $enabledModes, int $userId, ?float $score,
                                        int $perPage = 50): LengthAwarePaginator
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        $query = Rating::where('user_id', $userId)
            ->with(['beatmap.set', 'beatmap.creators.user', 'beatmap.creators.creatorName'])
            ->whereHas('beatmap', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray)
                    ->where('blacklisted', false);
            })
            ->orderByDesc('updated_at');

        if (!is_null($score)) {
            $query->whereRaw('score / 2 = '.$score);
        }

        return $query->paginate($perPage);
    }

    /**
     * Retrieves the users rating spread as an array of the rating bin (e.g. 0.5, 1.0) and the amount of ratings
     * in that bin.
     *
     * @param int $userId The user's ID.
     * @param int $enabledModes Bitfield of enabled modes.
     * @return Collection The user's rating spread.
     */
    public function getSpreadForUser(int $userId, int $enabledModes = 15): Collection
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return Rating::where('user_id', $userId)
            ->whereHas('beatmap', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray);
            })
            ->selectRaw('score as rating_bin, COUNT(*) as count')
            ->groupBy('rating_bin')
            ->orderBy('rating_bin')
            ->pluck('count', 'rating_bin');
    }
}
