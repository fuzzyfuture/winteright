<?php

namespace App\Services;

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    /**
     * Updates or creates a user object from a socialite user. If the user exists in winteright, their username and
     * avatar will be updated. If not, a new user object will be created for them.
     *
     * @param  SocialiteUser  $osuUser  The user object returned from Socialite
     * @return User The user object.
     */
    public function resolveUserFromOsu(SocialiteUser $osuUser): User
    {
        return User::updateOrCreate(
            ['id' => $osuUser->getId()],
            [
                'name' => $osuUser->getName(),
                'osu_access_token' => $osuUser->token,
                'osu_refresh_token' => $osuUser->refreshToken,
                'osu_token_expires_at' => now()->addSeconds($osuUser->expiresIn),
            ]
        );
    }
}
