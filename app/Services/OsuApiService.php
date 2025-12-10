<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\withToken;

class OsuApiService
{
    /**
     * Retrieves an access token for the osu! API with public scope. Intended for use while syncing beatmaps.
     * https://osu.ppy.sh/docs/index.html#authorization-code-grant
     *
     * @return string The access token.
     */
    public function getPublicAccessToken(): string
    {
        return Cache::remember('osu_public_api_token', 3600, function() {
            $response = Http::asForm()->post('https://osu.ppy.sh/oauth/token', [
                'client_id' => config('services.osu.client_id'),
                'client_secret' => config('services.osu.client_secret'),
                'grant_type' => 'client_credentials',
                'scope' => 'public',
            ]);

            return $response->json()['access_token'];
        });
    }

    /**
     * Retrieves the current osu! user with the specified token from the osu! API.
     * https://osu.ppy.sh/docs/index.html#get-own-data
     *
     * @param string $token An access token with identify scope.
     * @return array The user's JSON data.
     * @throws ConnectionException
     */
    public function getMe(string $token): array
    {
        $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/me');

        return $response->json();
    }

    /**
     * Searches beatmap sets using the osu! API.
     * https://osu.ppy.sh/docs/index.html#search-beatmapset
     *
     * @param string $token An access token with public scope.
     * @param string $status The status of beatmap set to search for.
     * @param string $sort The sort order for the search results.
     * @param bool $nsfw Whether beatmap sets marked as "explicit" should be included.
     * @param string|null $cursor The cursor string, used for pagination.
     * @return array The search results as a JSON array.
     * @throws ConnectionException
     */
    public function searchBeatmapSets(string $token, string $status, string $sort, bool $nsfw,
                                      ?string $cursor = null): array
    {
        $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/beatmapsets/search', [
           'status' => $status,
           'sort' => $sort,
           'nsfw' => $nsfw ? 'true' : 'false',
           'cursor_string' => $cursor,
        ]);

        return $response->json();
    }

    /**
     * Retrieves a beatmap set's "full details" object from the osu! API.
     * https://osu.ppy.sh/docs/index.html#get-apiv2beatmapsetsbeatmapset
     *
     * @param string $token An access token with public scope.
     * @param int $setId The ID of the beatmap set to retrieve.
     * @return array The beatmap set's JSON data.
     * @throws ConnectionException
     */
    public function getBeatmapSetFullDetails(string $token, int $setId): array
    {
        $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/beatmapsets/'.$setId);

        return $response->json();
    }

    /**
     * Retrieves scores of the specified user with the specified type from the osu! API.
     * https://osu.ppy.sh/docs/index.html#get-user-scores
     *
     * @param string $token An access token with public scope.
     * @param int $id The user's ID.
     * @param string $type The type of score to retrieve. Must be 'best', 'firsts', or 'recent'.
     * @param int $limit The maximum number of scores to retrieve.
     * @return array The user's scores as a JSON array.
     * @throws ConnectionException
     */
    public function getUserScores(string $token, int $id, string $type, int $limit = 50): array
    {
        $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/users/'.$id.'/scores/'.$type, [
            'limit' => $limit
        ]);

        return $response->json();
    }
}
