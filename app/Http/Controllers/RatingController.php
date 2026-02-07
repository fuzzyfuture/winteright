<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ratings\UpdateRatingRequest;
use App\Services\RatingService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RatingController extends Controller
{
    protected RatingService $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    public function update(UpdateRatingRequest $request, int $beatmapId)
    {
        $userId = Auth::id();

        if ($request->score === null || $request->score === '') {
            try {
                $this->ratingService->clear($userId, $beatmapId);
            } catch (Throwable $e) {
                return back()->withErrors('error while clearing rating for beatmap '.$beatmapId.': '
                    .$e->getMessage());
            }

            return back()->with('success', 'rating removed.');
        }

        try {
            $this->ratingService->set($userId, $beatmapId, $request->score);
        } catch (Throwable $e) {
            return back()->withErrors('error while rating '.$beatmapId.': '.$e->getMessage());
        }

        return back()->with('success', 'rating saved!');
    }
}
