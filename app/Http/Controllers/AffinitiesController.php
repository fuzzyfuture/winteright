<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class AffinitiesController extends Controller
{
    public function __construct(
        protected UserService $userService,
    ) {}

    public function mappers()
    {
        $user = Auth::user();
        $topRatedMappers = $this->userService->getTopRatedMappersForUserPaginated($user->id);

        return view('affinities.mappers', [
            'user' => $user,
            'topRatedMappers' => $topRatedMappers,
        ]);
    }
}
