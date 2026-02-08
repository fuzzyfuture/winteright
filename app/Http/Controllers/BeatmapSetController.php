<?php

namespace App\Http\Controllers;

use App\Http\Requests\BeatmapSets\AddBeatmapSetRequest;
use App\Services\BeatmapService;
use App\Services\BeatmapSyncService;
use App\Services\CommentService;
use App\Services\RatingService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BeatmapSetController extends Controller
{
    protected BeatmapService $beatmapService;
    protected RatingService $ratingService;
    protected CommentService $commentService;
    protected BeatmapSyncService $beatmapSyncService;

    public function __construct(BeatmapService $beatmapService, RatingService $ratingService,
        CommentService $commentService, BeatmapSyncService $beatmapSyncService)
    {
        $this->beatmapService = $beatmapService;
        $this->ratingService = $ratingService;
        $this->commentService = $commentService;
        $this->beatmapSyncService = $beatmapSyncService;
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
        $ratings->withPath('/mapsets/' . $setId . '/ratings');

        $comments = $this->commentService->getAllForBeatmapSet($setId, Auth::user() && Auth::user()->isAdmin());

        $ratingOptions = ['' => 'unrated', 0 => '0.0', 1 => '0.5', 2 => '1.0', 3 => '1.5', 4 => '2.0', 5 => '2.5',
            6 => '3.0', 7 => '3.5', 8 => '4.0', 9 => '4.5', 10 => '5.0'];

        return view('beatmaps.show', [
            'beatmapSet' => $beatmapSet,
            'ratings' => $ratings,
            'comments' => $comments,
            'ratingOptions' => $ratingOptions,
        ]);
    }

    public function ratings($setId)
    {
        $beatmapSet = $this->beatmapService->getBeatmapSet($setId);
        $beatmapIds = $beatmapSet->beatmaps->pluck('id');

        $ratings = $this->ratingService->getForBeatmaps($beatmapIds, Auth::user()->enabled_modes ?? 15, 10);
        $ratings->withPath('/mapsets/' . $setId . '/ratings');

        return view('beatmaps._ratings', ['ratings' => $ratings]);
    }

    public function add()
    {
        return view('beatmaps.add');
    }

    public function postAdd(AddBeatmapSetRequest $request)
    {
        $userId = Auth::id();
        $validated = $request->validated();

        try {
            $beatmapSet = $this->beatmapSyncService->syncBeatmapSetFromUrl($validated['url'], $userId);
        } catch (Throwable $e) {
            return back()->withErrors('error syncing beatmap set at ' . $validated['url'] . ': ' . $e->getMessage());
        }

        return redirect()->route('beatmaps.show', ['set' => $beatmapSet->id])->with('success', 'beatmap set synced successfully!');
    }

    public function sync($setId)
    {
        $userId = Auth::id();

        try {
            $this->beatmapSyncService->syncBeatmapSet($setId, $userId);
        } catch (Throwable $e) {
            return back()->withErrors('error syncing beatmap set: ' . $e->getMessage());
        }

        return back()->with('success', 'beatmap set synced successfully!');
    }
}
