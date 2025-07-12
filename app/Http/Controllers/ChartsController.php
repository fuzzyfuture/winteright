<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\ChartsService;

class ChartsController extends Controller
{
    protected ChartsService $chartsService;
    protected BeatmapService $beatmapService;

    public function __construct(ChartsService $chartsService, BeatmapService $beatmapService)
    {
        $this->chartsService = $chartsService;
        $this->beatmapService = $beatmapService;
    }

    public function index()
    {
        $topBeatmaps = $this->chartsService->getTopAllTime();
        $this->beatmapService->applyCreatorLabels($topBeatmaps->getCollection());
        return view('charts.index', compact('topBeatmaps'));
    }
}
