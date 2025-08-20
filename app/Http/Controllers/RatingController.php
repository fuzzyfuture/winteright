<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Services\RatingService;
use App\Validators\RatingValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Throwable;

class RatingController extends Controller
{
    protected RatingService $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    public function update(Request $request, RatingValidator $validator, int $beatmapId)
    {
        $userId = Auth::id();

        if (!$validator->validate($request->all(['score']), 'update')) {
            return back()->withErrors($validator);
        }

        if ($request->score === null || $request->score === '') {
            try {
                $this->ratingService->clear($userId, $beatmapId);
            } catch (Throwable $e) {
                return back()->withErrors('Error while clearing rating for beatmap '.$beatmapId.': '.$e->getMessage());
            }

            return back()->with('success', 'Rating removed.');
        }

        try {
            $this->ratingService->set($userId, $beatmapId, $validated['score']);
        } catch (Throwable $e) {
            return back()->withErrors('Error while rating '.$beatmapId.': '.$e->getMessage());
        }

        return back()->with('success', 'Rating saved!');
    }
}
