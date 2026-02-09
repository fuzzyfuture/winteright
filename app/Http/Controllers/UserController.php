<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ratings\GetUserRatingsRequest;
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

        return view('users.show', [
            'user' => $user,
            'ratingSpread' => $ratingSpread,
            'recentRatings' => $recentRatings,
            'lists' => $lists,
            'beatmapSets' => $beatmapSets,
            'guestDifficulties' => $guestDifficulties,
            'topRatedMappers' => $topRatedMappers,
        ]);
    }

    public function ratings(GetUserRatingsRequest $request, int $id)
    {
        $user = $this->userService->get($id);
        $enabledModes = Auth::user()->enabled_modes ?? 15;
        $score = $request->query('score');
        $srMin = $request->query('sr_min');
        $srMax = $request->query('sr_max');
        $yearMin = $request->query('year_min');
        $yearMax = $request->query('year_max');
        $mapperNameOrId = $request->query('mapper');
        $sort = $request->query('sort');
        $sortDirection = $request->query('sort_dir');

        $ratings = $this->ratingService->getForUserPaginated($enabledModes, $id, $score, $srMin, $srMax, $yearMin,
            $yearMax, $mapperNameOrId, $sort, $sortDirection, Auth::id() === $id);
        $ratings->appends($request->query());

        $ratingOptions = ['' => 'any', '0.0' => '0.0', '0.5' => '0.5', '1.0' => '1.0', '1.5' => '1.5', '2.0' => '2.0', '2.5' => '2.5',
            '3.0' => '3.0', '3.5' => '3.5', '4.0' => '4.0', '4.5' => '4.5', '5.0' => '5.0'];
        $yearOptions = ['' => 'any'] + $this->beatmapService->getBeatmapYears()
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->toArray();
        $sortOptions = ['' => 'rated date', 'score' => 'score', 'sr' => 'star rating',
            'ranked_date' => 'ranked date'];
        $sortDirectionOptions = ['desc' => 'desc', 'asc' => 'asc'];

        return view('users.ratings', [
            'user' => $user,
            'ratings' => $ratings,
            'score' => $score,
            'srMin' => $srMin,
            'srMax' => $srMax,
            'yearMin' => $yearMin,
            'yearMax' => $yearMax,
            'mapperNameOrId' => $mapperNameOrId,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'ratingOptions' => $ratingOptions,
            'yearOptions' => $yearOptions,
            'sortOptions' => $sortOptions,
            'sortDirectionOptions' => $sortDirectionOptions,
        ]);
    }

    public function lists(int $id)
    {
        $user = $this->userService->get($id);
        $lists = $this->userListService->getForUserPaginated($id, Auth::check() && Auth::id() == $id);

        return view('users.lists', ['user' => $user, 'lists' => $lists]);
    }

    public function mapsets(Request $request, int $id)
    {
        $user = $this->userService->get($id);
        $enabledModes = Auth::user()->enabled_modes ?? 15;
        $page = $request->get('page') ?? 1;

        $mapsets = $this->beatmapService->getBeatmapSetsForUserPaginated($id, $enabledModes, $page);

        return view('users.mapsets', ['user' => $user, 'mapsets' => $mapsets]);
    }

    public function gds(Request $request, int $id)
    {
        $user = $this->userService->get($id);
        $enabledModes = Auth::user()->enabled_modes ?? 15;
        $page = $request->get('page') ?? 1;

        $gds = $this->beatmapService->getGuestDifficultiesForUserPaginated($id, $enabledModes, $page);

        return view('users.gds', ['user' => $user, 'gds' => $gds]);
    }
}
