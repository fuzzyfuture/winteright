<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Models\Beatmap;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ChartsService
{
    public function getTopBeatmaps($year = null, $excludeRated = false, $user = null, $offset = 0, $limit = 50): Collection
    {
        return $this->topBeatmapsBaseQuery($year, $excludeRated, $user)
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    public function getTopBeatmapsCount($year = null, $excludeRated = false, $user = null): int
    {
        return $this->topBeatmapsBaseQuery($year, $excludeRated, $user)->count();
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
