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

    public function show(string|int $id)
    {
        $user = User::where('id', $id)->firstOrFail();

        return view('users.show', $this->userService->getProfileData($user));
    }
}
