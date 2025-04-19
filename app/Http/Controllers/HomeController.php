<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\SiteInfoService;
use App\Services\StatsService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected StatsService $statsService;
    protected BeatmapService $beatmapService;
    protected SiteInfoService $siteInfoService;

    public function __construct(StatsService $statsService, BeatmapService $beatmapService, SiteInfoService $siteInfoService)
    {
        $this->statsService = $statsService;
        $this->beatmapService = $beatmapService;
        $this->siteInfoService = $siteInfoService;
    }

    public function index()
    {
        $user = Auth::user();
        $stats = $this->statsService->getHomePageStats();
        $recentlyRanked = $this->beatmapService->getRecentBeatmaps();
        $lastSynced = $this->siteInfoService->getLastSyncedRankedBeatmaps();

        return view('home', compact('user', 'stats', 'recentlyRanked', 'lastSynced'));
    }
}
