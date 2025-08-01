<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\BeatmapService;
use App\Services\RatingService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;
    protected RatingService $ratingService;
    protected BeatmapService $beatmapService;

    public function __construct(UserService $userService, RatingService $ratingService, BeatmapService $beatmapService)
    {
        $this->userService = $userService;
        $this->ratingService = $ratingService;
        $this->beatmapService = $beatmapService;
    }

    public function show(int $id)
    {
        $user = $this->userService->get($id);

        return view('users.show', $this->userService->getProfileData($user));
    }

    public function ratings(Request $request, int $id)
    {
        $user = $this->userService->get($id);

        $validScores = ['0.0', '0.5', '1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0'];
        $score = $request->query('score');

        if (!is_null($score) && !in_array($score, $validScores)) {
            $score = '0.0';
        }

        $ratings = $this->ratingService->getForUser($id, $score ? floatval($score) : null);
        $ratings->appends($request->query());

        $this->beatmapService->applyCreatorLabels($ratings->getCollection()->pluck('beatmap'));

        return view('users.ratings', compact('user', 'ratings', 'score'));
    }
}
