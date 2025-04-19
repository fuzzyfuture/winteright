<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\Beatmap;
use App\Models\BeatmapSet;

class StatsService
{
    /**
     * Retrieves stats for the home page. Includes DB totals for the amount of ratings, beatmaps, and beatmap sets.
     * @return array The stats for the home page.
     */
    public function getHomePageStats(): array
    {
        return [
            'ratings' => Rating::count(),
            'beatmaps' => Beatmap::count(),
            'beatmapSets' => BeatmapSet::count(),
        ];
    }
}
