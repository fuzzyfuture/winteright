<?php

namespace App\Services;

use App\Models\Beatmap;
use Illuminate\Pagination\LengthAwarePaginator;

class ChartsService
{
    /**
     * Retrieves paginated data for the top beatmaps of all-time. Intended for display on the charts page.
     * @param int $perPage The amount of results to display per page.
     * @param int $maxPages The maximum amount of pages to display.
     * @return LengthAwarePaginator The paginated data.
     */
    public function getTopAllTime(int $perPage = 25, int $maxPages = 50): LengthAwarePaginator
    {
        $page = request()->input('page', 1);
        $maxResults = $perPage * $maxPages;

        $all = Beatmap::with(['set', 'userRating'])
            ->where('blacklisted', false)
            ->where('rating_count', '>', 0)
            ->orderBy('bayesian_avg', 'desc')
            ->limit($maxResults)
            ->get();

        return new LengthAwarePaginator(
            $all->forPage($page, $perPage),
            $all->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
}
