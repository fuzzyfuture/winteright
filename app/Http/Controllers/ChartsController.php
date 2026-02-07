<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\ChartsService;
use App\Services\SiteInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChartsController extends Controller
{
    protected ChartsService $chartsService;
    protected BeatmapService $beatmapService;
    protected SiteInfoService $siteInfoService;

    public function __construct(ChartsService $chartsService, BeatmapService $beatmapService, SiteInfoService $siteInfoService)
    {
        $this->chartsService = $chartsService;
        $this->beatmapService = $beatmapService;
        $this->siteInfoService = $siteInfoService;
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $year = $request->query('year');
        $excludeRated = $request->query('exclude_rated');
        $enabledModes = Auth::user()->enabled_modes ?? 15;

        $topBeatmaps = $this->chartsService->getTopBeatmapsPaginated($enabledModes, $year, $excludeRated, Auth::id(),
            $page);
        $topBeatmaps->appends($request->query());

        $yearOptions = ['' => 'all'] + $this->beatmapService->getBeatmapYears()
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->toArray();

        $lastUpdated = $this->siteInfoService->getLastUpdatedCharts();

        return view('charts.index', compact('topBeatmaps', 'yearOptions', 'year',
            'excludeRated', 'lastUpdated'));
    }
}
