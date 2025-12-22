<?php

namespace App\Providers;

use App\Helpers\OsuUrl;
use App\Services\OsuApiService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\ConnectionException;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class OsuSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['public', 'identify'];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(OsuUrl::apiOauthAuthorize(), $state);
    }

    protected function getTokenUrl(): string
    {
        return OsuUrl::apiOauthToken();
    }

    protected function getCodeFields($state = null) : array
    {
        return [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'scope' => implode(' ', $this->scopes),
            'state' => $state,
        ];
    }

    protected function getTokenFields($code) : array
    {
        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
     * @throws ConnectionException
     */
    protected function getUserByToken($token)
    {
        return app(OsuApiService::class)->getMe($token);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['username']
        ]);
    }
}
