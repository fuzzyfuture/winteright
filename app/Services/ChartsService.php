<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Models\Beatmap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ChartsService
{
    /**
     * Retrieves top beatmaps for the charts with filter parameters.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param ?string $year The year to filter beatmaps to.
     * @param bool $excludeRated True to exclude maps that the user has already rated.
     * @param ?int $userId The user's ID.
     * @param int $offset The offset for results pagination.
     * @param int $limit The amount to display per-page.
     * @return Collection The top beatmaps with the specified filter parameters.
     */
    public function getTopBeatmaps(int $enabledModes, ?string $year = null, ?bool $excludeRated = false,
                                   ?int $userId = null, int $offset = 0, int $limit = 50): Collection
    {
        $query = $this->topBeatmapsBaseQuery($enabledModes, $year, $excludeRated, $userId)
            ->skip($offset)
            ->take($limit);

        if ($excludeRated && $userId) {
            return $query->get();
        }

        return Cache::tags('charts')->remember('top_beatmaps_'.$enabledModes.'_'.$year.'_'.$offset.'_'.$limit, 43200, function () use ($query) {
           return $query->get();
        });
    }

    /**
     * Retrieves the count of top beatmaps for the charts with filter parameters.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param ?string $year The year to filter beatmaps to.
     * @param bool $excludeRated True to exclude maps that the user has already rated.
     * @param ?int $userId The user's ID.
     * @return int The count of top beatmaps with the specified filter parameters.
     */
    public function getTopBeatmapsCount(int $enabledModes, ?string $year = null, ?bool $excludeRated = false,
                                        ?int $userId = null): int
    {
        return $this->topBeatmapsBaseQuery($enabledModes, $year, $excludeRated, $userId)->count();
    }

    /**
     * Base database query builder for retrieving filtered top beatmap results.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param ?string $year The year to filter beatmaps to.
     * @param bool $excludeRated True to exclude maps that the user has already rated.
     * @param ?int $userId The user's ID.
     * @return Builder The query builder.
     */
    private function topBeatmapsBaseQuery(int $enabledModes, ?string $year = null, ?bool $excludeRated = false,
                                          ?int $userId = null): Builder
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);
        $query = Beatmap::with(['set', 'userRating'])
            ->withCount('ratings')
            ->where('blacklisted', false)
            ->whereHas('ratings')
            ->whereIn('mode', $modesArray)
            ->orderBy('bayesian_avg', 'desc');

        if ($year) {
            $query->whereHas('set', function ($query) use ($year) {
                $query->whereYear('date_ranked', $year);
            });
        }

        if ($excludeRated && $userId) {
            $query->whereDoesntHave('ratings', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
        }

        return $query;
    }
}
