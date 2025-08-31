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
        $excludeRated = $request->query('exclude_rated');

        $perPage = 50;
        $maxPages = 200;
        $maxResults = $perPage * $maxPages;

        $offset = ($page - 1) * $perPage;

        $enabledModes = Auth::user()->enabled_modes ?? 15;

        $actualCount = $this->chartsService->getTopBeatmapsCount($enabledModes, $year, $excludeRated, Auth::id());
        $totalResults = min($actualCount, $maxResults);

        $beatmapData = $this->chartsService->getTopBeatmaps($enabledModes, $year, $excludeRated, Auth::id(), $offset, $perPage);

        $topBeatmaps = new LengthAwarePaginator(
            $beatmapData,
            $totalResults,
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        $topBeatmaps->appends($request->query());

        $this->beatmapService->applyCreatorLabels($topBeatmaps->getCollection());

        $yearOptions = ['' => 'all'];
        $beatmapYears = $this->beatmapService->getBeatmapYears();

        foreach ($beatmapYears as $beatmapYear) {
            $yearOptions[$beatmapYear] = $beatmapYear;
        }

        return view('charts.index', compact('topBeatmaps', 'yearOptions', 'year',
            'excludeRated'));
    }
}
