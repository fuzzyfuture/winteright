<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Models\Beatmap;
use App\Models\Rating;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ChartsService
{
    /**
     * Retrieves top beatmaps for the charts with filter parameters.
     *
     * @param  int  $enabledModes  Bitfield of enabled modes.
     * @param  ?string  $year  The year to filter beatmaps to.
     * @param  bool  $excludeRated  True to exclude maps that the user has already rated.
     * @param  ?int  $userId  The user's ID.
     * @param  int  $page  The current page.
     * @param  int  $perPage  The amount of results to include per-page.
     * @param  int  $maxPages  The maximum amount of pages.
     * @return LengthAwarePaginator The top beatmaps with the specified filter parameters.
     */
    public function getTopBeatmapsPaginated(int $enabledModes, ?string $year = null, ?bool $excludeRated = false,
        ?int $userId = null, int $page = 1, int $perPage = 50,
        int $maxPages = 200): LengthAwarePaginator
    {
        $maxResults = $perPage * $maxPages;
        $offset = ($page - 1) * $perPage;
        $actualCount = $this->getTopBeatmapsCount($enabledModes, $year, $excludeRated, $userId);
        $totalResults = min($maxResults, $actualCount);

        $beatmaps = $this->topBeatmapsBaseQuery($enabledModes, $year, $excludeRated, $userId)
            ->skip($offset)
            ->take($perPage);

        if ($userId && $excludeRated) {
            $beatmaps = $beatmaps->get();
        } else {
            $beatmaps = Cache::tags('charts')->remember(
                'beatmaps:top:'.$enabledModes.':'.$year.':'.$page,
                43200,
                function () use ($beatmaps) {
                    return $beatmaps->get();
                });
        }

        if ($userId) {
            $beatmaps->load('userRating');
        }

        return new LengthAwarePaginator(
            $beatmaps,
            $totalResults,
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }

    /**
     * Retrieves the count of top beatmaps for the charts with filter parameters.
     *
     * @param  int  $enabledModes  Bitfield of enabled modes.
     * @param  ?string  $year  The year to filter beatmaps to.
     * @param  bool  $excludeRated  True to exclude maps that the user has already rated.
     * @param  ?int  $userId  The user's ID.
     * @return int The count of top beatmaps with the specified filter parameters.
     */
    public function getTopBeatmapsCount(int $enabledModes, ?string $year = null, ?bool $excludeRated = false,
        ?int $userId = null): int
    {
        $query = $this->topBeatmapsBaseQuery($enabledModes, $year, $excludeRated, $userId);

        if ($excludeRated && $userId) {
            return $query->count();
        }

        return Cache::tags('charts')->remember('top_beatmaps_count:'.$enabledModes.':'.$year, 43200, function () use ($query) {
            return $query->count();
        });
    }

    /**
     * Recalculates the bayesian average (which determines chart position) for every beatmap.
     */
    public function recalculateBayesianAverages(): void
    {
        $totalRatings = Rating::count();
        $averageRating = Rating::avg('score') ?? 0;

        if ($totalRatings === 0) {
            return;
        }

        DB::statement('
            UPDATE beatmaps
            INNER JOIN (
                SELECT
                    beatmap_id,
                    COUNT(*) as ratings_count,
                    SUM(score) as total_score
                FROM ratings
                GROUP BY beatmap_id
            ) r ON beatmaps.id = r.beatmap_id
            SET beatmaps.bayesian_avg = ((? * ?) + r.total_score) / (? + r.ratings_count)
        ', [$averageRating, $totalRatings, $totalRatings]);

        app(SiteInfoService::class)->storeLastUpdatedCharts(Carbon::now()->toDateTimeString());
        Cache::tags(['charts'])->flush();
    }

    /**
     * Base database query builder for retrieving filtered top beatmap results.
     *
     * @param  int  $enabledModes  Bitfield of enabled modes.
     * @param  ?string  $year  The year to filter beatmaps to.
     * @param  bool  $excludeRated  True to exclude maps that the user has already rated.
     * @param  ?int  $userId  The user's ID.
     * @return Builder The query builder.
     */
    private function topBeatmapsBaseQuery(int $enabledModes, ?string $year = null, ?bool $excludeRated = false,
        ?int $userId = null): Builder
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);
        $query = Beatmap::with(['set', 'creators.user', 'creators.creatorName'])
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
