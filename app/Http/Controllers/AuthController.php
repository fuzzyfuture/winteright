<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\OsuSocialiteProvider;
use App\Services\AuthService;
use GuzzleHttp\Exception\ClientException;
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
        /** @var OsuSocialiteProvider $driver */
        $driver = Socialite::driver('osu');

        return $driver->stateless()->redirect();
    }

    public function callback()
    {
        /** @var OsuSocialiteProvider $driver */
        $driver = Socialite::driver('osu');

        try {
            $osuUser = $driver->stateless()->user();
        } catch (ClientException) {
            return redirect()->intended(route('home'));
        }

        $user = $this->authService->resolveUserFromOsu($osuUser);

        Auth::login($user);

        return redirect()->intended(route('home'))->with('success', 'login successful!');
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
        }

        return redirect()->intended(route('home'))->with('success', 'logout successful!');
    }
}
