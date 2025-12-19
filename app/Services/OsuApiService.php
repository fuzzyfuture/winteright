<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
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
        return Cache::remember('osu_public_api_token', 86400, function() {
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
     * Retrieves a user's access token for the osu! API. Intended for use while making API requests on behalf of a
     * specific user. If the user does not currently have an access token, it will be refreshed.
     *
     * @param int $userId The user's ID.
     * @return string The user's valid access token.
     * @throws ConnectionException
     * @throws AuthenticationException
     * @throws Throwable
     */
    public function getIdentifiedAccessToken(int $userId): string
    {
        $user = app(UserService::class)->get($userId);

        if ($user->osu_access_token && $user->osu_token_expires_at > now()) {
            return $user->osu_access_token;
        }

        if ($user->osu_refresh_token) {
            return $this->refreshIdentifiedAccessToken($user);
        }

        throw new AuthenticationException('Access token and refresh token both missing; re-authentication required.');
    }

    /**
     * Refreshes a user's OAuth tokens and returns their new access token.
     *
     * @param User $user The user whose tokens need to be refreshed.
     * @throws ConnectionException
     * @throws Throwable
     */
    private function refreshIdentifiedAccessToken(User $user): string
    {
        $response = Http::asForm()->post('https://osu.ppy.sh/oauth/token', [
            'client_id' => config('services.osu.client_id'),
            'client_secret' => config('services.osu.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $user->osu_refresh_token,
        ]);

        $data = $response->json();

        app(UserService::class)->updateOsuTokens($user->id, $data['access_token'], $data['refresh_token'],
            now()->addSeconds($data['expires_in']));

        return $data['access_token'];
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
     * Retrieves user info from the osu! API.
     * https://osu.ppy.sh/docs/index.html#get-user
     *
     * @param int $id The user's ID.
     * @return array The user's info, as a JSON array.
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws Throwable
     */
    public function getUser(int $id): array
    {
        $token = $this->getIdentifiedAccessToken($id);
        $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/users/'.$id);

        return $response->json();
    }

    /**
     * Retrieves scores of the specified user with the specified type from the osu! API.
     * https://osu.ppy.sh/docs/index.html#get-user-scores
     *
     * @param int $id The user's ID.
     * @param string $type The type of score to retrieve. Must be 'best', 'firsts', or 'recent'.
     * @param int $limit The maximum number of scores to retrieve.
     * @return array The user's scores as a JSON array.
     * @throws ConnectionException
     * @throws AuthenticationException
     * @throws Throwable
     */
    public function getUserScores(int $id, string $type, int $limit = 100): array
    {
        $token = $this->getIdentifiedAccessToken($id);
        $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/users/'.$id.'/scores/'.$type, [
            'limit' => $limit
        ]);

        return $response->json();
    }

    /**
     * Retrieves beatmap sets for a specific user via the osu! API.
     * https://osu.ppy.sh/docs/index.html#get-user-beatmaps
     *
     * @param int $id The user's ID.
     * @param string $type The type of beatmaps to retrieve. See osu! API documentation for available types.
     * @param int $limit The maximum number of results to retrieve.
     * @param int $offset The result offset for pagination.
     * @return array The user's favorite beatmap sets as a JSON array.
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws Throwable
     */
    public function getUserBeatmaps(int $id, string $type, int $limit = 100, int $offset = 0): array
    {
        $token = $this->getIdentifiedAccessToken($id);
        $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/users/'.$id.'/beatmapsets/'.$type, [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $response->json();
    }
}
