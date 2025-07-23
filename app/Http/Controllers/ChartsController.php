<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\ChartsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ChartsController extends Controller
{
    protected ChartsService $chartsService;
    protected BeatmapService $beatmapService;

    public function __construct(ChartsService $chartsService, BeatmapService $beatmapService)
    {
        $this->chartsService = $chartsService;
        $this->beatmapService = $beatmapService;
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $year = $request->query('year');
        $excludeRated = $request->query('excludeRated');

        $perPage = 50;
        $maxPages = 200;
        $maxResults = $perPage * $maxPages;

        $offset = ($page - 1) * $perPage;

        $actualCount = $this->chartsService->getTopBeatmapsCount($year, $excludeRated, Auth::user());
        $totalResults = min($actualCount, $maxResults);

        $beatmapData = $this->chartsService->getTopBeatmaps($year, $excludeRated, Auth::user(), $offset, $perPage);

        $topBeatmaps = new LengthAwarePaginator(
            $beatmapData,
            $totalResults,
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        $topBeatmaps->appends($request->query());

        $this->beatmapService->applyCreatorLabels($topBeatmaps->getCollection());
        $beatmapYears = $this->beatmapService->getBeatmapYears();

        return view('charts.index', compact('topBeatmaps', 'beatmapYears', 'year',
            'excludeRated'));
    }
}
