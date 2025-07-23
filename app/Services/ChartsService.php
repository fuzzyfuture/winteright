<?php

namespace App\Services;

use App\Models\Beatmap;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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

    private function topBeatmapsBaseQuery($year = null, $excludeRated = false, $user = null)
    {
        $query = Beatmap::with(['set', 'userRating'])
            ->withCount('ratings')
            ->where('blacklisted', false)
            ->whereHas('ratings')
            ->orderBy('bayesian_avg', 'desc');

        if ($year) {
            $query->whereHas('set', function ($query) use ($year) {
                $query->whereYear('date_ranked', $year);
            });
        }

        if ($excludeRated && $user) {
            $query->whereDoesntHave('ratings', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }

        return $query;
    }
}
