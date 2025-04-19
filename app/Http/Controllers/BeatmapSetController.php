<?php

namespace App\Http\Controllers;

use App\Models\BeatmapSet;
use App\Services\BeatmapService;
use Illuminate\Http\Request;

class BeatmapSetController extends Controller
{
    protected BeatMapService $beatmapService;

    public function __construct(BeatmapService $beatmapService)
    {
        $this->beatmapService = $beatmapService;
    }

    public function show($setId)
    {
        $beatmapSet = $this->beatmapService->getBeatmapSet($setId);
        return view('beatmaps.show', compact('beatmapSet'));
    }
}
