<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Services\RatingService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    protected RatingService $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    public function update(Request $request, int $beatmapId)
    {
        $userId = auth()->id();

        if ($request->score === null || $request->score === '') {
            $this->ratingService->clear($userId, $beatmapId);
            return back()->with('success', 'Rating removed.');
        }

        $validated = $request->validate([
            'score' => ['required', 'integer', 'between:0,10'],
        ]);

        $this->ratingService->set($userId, $beatmapId, $validated['score']);

        return back()->with('success', 'Rating saved!');
    }
}
