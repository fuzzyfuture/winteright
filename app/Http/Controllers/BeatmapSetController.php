<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\RatingService;
use Illuminate\Support\Facades\Auth;

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

        $ratings = $this->ratingService->getForBeatmaps($beatmapIds, Auth::user()->enabled_modes ?? 15, 10);
        $ratings->withPath('/mapsets/'.$setId.'/ratings');

        $this->beatmapService->applyCreatorLabels($beatmapSet->beatmaps);

        $ratingOptions = ['' => 'unrated', 0 => '0.0', 1 => '0.5', 2 => '1.0', 3 => '1.5', 4 => '2.0', 5 => '2.5',
            6 => '3.0', 7 => '3.5', 8 => '4.0', 9 => '4.5', 10 => '5.0'];

        return view('beatmaps.show', compact('beatmapSet', 'ratings', 'ratingOptions'));
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
