<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OsuApiService
{
    /**
     * Retrieves an access token for the osu! API with public scope. Intended for use while syncing beatmaps.
     * @return string The access token.
     */
    public function getAccessToken(): string
    {
        return Cache::remember('osu_api_token', 3000, function() {
            $response = Http::asForm()->post('https://osu.ppy.sh/oauth/token', [
                'client_id' => config('services.osu.client_id'),
                'client_secret' => config('services.osu.client_secret'),
                'grant_type' => 'client_credentials',
                'scope' => 'public',
            ]);

            return $response->json()['access_token'];
        });
    }
}
