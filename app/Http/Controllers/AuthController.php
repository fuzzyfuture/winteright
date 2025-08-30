<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function loginRedirect()
    {
        session(['url.intended' => url()->previous()]);
        return redirect()->route('auth.redirect');
    }

    public function redirect()
    {
        return Socialite::driver('osu')->redirect();
    }

    public function callback()
    {
        $osuUser = Socialite::driver('osu')->user();
        $user = $this->authService->resolveUserFromOsu($osuUser);

        Auth::login($user);

        return redirect()->intended(route('home'));
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
        }

        return redirect()->intended(route('home'));
    }
}
