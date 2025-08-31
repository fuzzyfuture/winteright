<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\RatingService;
use App\Services\SiteInfoService;
use App\Services\StatsService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected StatsService $statsService;
    protected BeatmapService $beatmapService;
    protected SiteInfoService $siteInfoService;
    protected RatingService $ratingService;

    public function __construct(StatsService $statsService, BeatmapService $beatmapService,
                                SiteInfoService $siteInfoService, RatingService $ratingService)
    {
        $this->statsService = $statsService;
        $this->beatmapService = $beatmapService;
        $this->siteInfoService = $siteInfoService;
        $this->ratingService = $ratingService;
    }

    public function index()
    {
        $user = Auth::user();
        $enabledModes = $user->enabled_modes ?? 15;

        $stats = $this->statsService->getHomePageStats();
        $recentlyRanked = $this->beatmapService->getRecentBeatmapSets($enabledModes);
        $recentRatings = $this->ratingService->getRecent($enabledModes);
        $lastSynced = $this->siteInfoService->getLastSyncedRankedBeatmaps();

        return view('home', compact('user', 'stats', 'recentlyRanked', 'recentRatings',
            'lastSynced'));
    }
}
