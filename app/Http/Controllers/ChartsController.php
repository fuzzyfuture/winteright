<?php

namespace App\Http\Controllers;

use App\Services\ChartsService;
use Illuminate\Http\Request;

class ChartsController extends Controller
{
    protected ChartsService $beatmapService;

    public function __construct(ChartsService $beatmaps)
    {
        $this->beatmapService = $beatmaps;
    }

    public function index()
    {
        $topBeatmaps = $this->beatmapService->getTopAllTime();
        return view('charts.index', compact('topBeatmaps'));
    }
}
