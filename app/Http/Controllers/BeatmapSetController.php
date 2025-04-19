<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;

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
        $beatmapSet->beatmaps = $beatmapSet->beatmaps
            ->sortBy([
                ['mode', 'asc'],
                ['sr', 'desc'],
            ])
            ->values();

        $diffCreatorLabels = $this->beatmapService->getCreatorLabelsForManyBeatmaps($beatmapSet->beatmaps);

        return view('beatmaps.show', compact('beatmapSet', 'diffCreatorLabels'));
    }
}
