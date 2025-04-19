<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function showByOsuId(string|int $osuId)
    {
        $user = User::where('osu_id', $osuId)->firstOrFail();

        return view('users.show', $this->userService->getProfileData($user));
    }
}
