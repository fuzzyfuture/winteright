<?php

namespace App\Http\Controllers;

use App\Services\BeatmapService;
use App\Services\RatingService;
use App\Services\UserListService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected UserService $userService;

    protected RatingService $ratingService;

    protected BeatmapService $beatmapService;

    protected UserListService $userListService;

    public function __construct(UserService $userService, RatingService $ratingService, BeatmapService $beatmapService,
        UserListService $userListService)
    {
        $this->userService = $userService;
        $this->ratingService = $ratingService;
        $this->beatmapService = $beatmapService;
        $this->userListService = $userListService;
    }

    public function show(string $id)
    {
        $user = is_numeric($id) ?
            $this->userService->get($id) :
            $this->userService->getByName($id);

        $enabledModes = Auth::user()->enabled_modes ?? 15;

        $recentRatings = $this->ratingService->getForUser($user->id, $enabledModes);
        $ratingSpread = $this->ratingService->getSpreadForUser($user->id, $enabledModes);
        $topRatedMappers = $this->userService->getTopRatedMappersForUser($user->id);
        $lists = $this->userListService->getForUser($user->id);
        $beatmapSets = $this->beatmapService->getBeatmapSetsForUser($user->id, $enabledModes);
        $guestDifficulties = $this->beatmapService->getGuestDifficultiesForUser($user->id, $enabledModes);

        return view('users.show', compact('user', 'ratingSpread', 'recentRatings', 'lists',
            'beatmapSets', 'guestDifficulties', 'topRatedMappers'));
    }

    public function ratings(Request $request, int $id)
    {
        $user = $this->userService->get($id);

        $validScores = ['0.0', '0.5', '1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0'];
        $score = $request->query('score');

        if (! is_null($score) && ! in_array($score, $validScores)) {
            $score = '0.0';
        }

        $enabledModes = Auth::user()->enabled_modes ?? 15;

        $ratings = $this->ratingService->getForUserPaginated($enabledModes, $id, $score ? floatval($score) : null);
        $ratings->appends($request->query());

        return view('users.ratings', compact('user', 'ratings', 'score'));
    }

    public function lists(int $id)
    {
        $user = $this->userService->get($id);
        $lists = $this->userListService->getForUserPaginated($id, Auth::check() && Auth::id() == $id);

        return view('users.lists', compact('user', 'lists'));
    }

    public function mapsets(Request $request, int $id)
    {
        $user = $this->userService->get($id);
        $enabledModes = Auth::user()->enabled_modes ?? 15;
        $page = $request->get('page') ?? 1;

        $mapsets = $this->beatmapService->getBeatmapSetsForUserPaginated($id, $enabledModes, $page);

        return view('users.mapsets', compact('user', 'mapsets'));
    }

    public function gds(Request $request, int $id)
    {
        $user = $this->userService->get($id);
        $enabledModes = Auth::user()->enabled_modes ?? 15;
        $page = $request->get('page') ?? 1;

        $gds = $this->beatmapService->getGuestDifficultiesForUserPaginated($id, $enabledModes, $page);

        return view('users.gds', compact('user', 'gds'));
    }
}
