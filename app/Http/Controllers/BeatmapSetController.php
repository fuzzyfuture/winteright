<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\RatingService;
use Illuminate\Http\Request;

class BeatmapSetController extends Controller
{
    protected BeatmapService $beatmapService;
    protected RatingService $ratingService;

    public function __construct(BeatmapService $beatmapService, RatingService $ratingService)
    {
        $this->beatmapService = $beatmapService;
        $this->ratingService = $ratingService;
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

        $beatmapIds = $beatmapSet->beatmaps->pluck('id');

        $ratings = $this->ratingService->getForBeatmaps($beatmapIds, 10);
        $ratings->withPath('/mapsets/'.$setId.'/ratings');

        $this->beatmapService->applyCreatorLabels($beatmapSet->beatmaps);

        return view('beatmaps.show', compact('beatmapSet', 'ratings'));
    }

    public function ratings($setId)
    {
        $beatmapSet = $this->beatmapService->getBeatmapSet($setId);
        
        $beatmapIds = $beatmapSet->beatmaps->pluck('id');

        $ratings = $this->ratingService->getForBeatmaps($beatmapIds, 10);
        $ratings->withPath('/mapsets/'.$setId.'/ratings');

        return view('partials.beatmapset.ratings', compact('ratings'));
    }
}
