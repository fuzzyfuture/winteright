<?php

namespace App\Services;

use App\DataObjects\TopRatedMapper;
use App\Enums\HideCommentsOption;
use App\Enums\HideRatingsOption;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;
use Throwable;

class UserService
{
    /**
     * Checks if a user with the given ID exists. Returns true if the user exists.
     *
     * @param  int  $id  The user ID to check.
     * @return bool True if the user exists.
     */
    public function exists(int $id): bool
    {
        return User::whereId($id)->exists();
    }

    /**
     * Retrieves the user with the given ID.
     *
     * @param  int  $id  The user's ID.
     * @return User The user.
     */
    public function get(int $id): User
    {
        return User::whereId($id)->firstOrFail();
    }

    /**
     * Retrieves the user with the specified username.
     *
     * @param  string  $name  The user's username.
     * @return User The user.
     */
    public function getByName(string $name): User
    {
        return user::whereName($name)->firstOrFail();
    }

    /**
     * Retrieves a user's name from their ID. Uses the beatmap creator names table as a fallback, if the user is not
     * a winteright user.
     *
     * @param  int  $id  The user's ID.
     * @return string The user's name.
     */
    public function getName(int $id): string
    {
        $user = User::whereId($id)->first();

        if ($user) {
            return $user->name;
        }

        return DB::table('beatmap_creator_names')->where('id', $id)->value('name');
    }

    /**
     * Retrieves a user's ID from their name. Uses the beatmap creator names table as a fallback, if the user is not
     * a winteright user.
     *
     * @param  string  $name  The user's name.
     * @return int The user's ID, or -1 if the user is not a winteright user or in the beatmap creator names table.
     */
    public function getIdByName(string $name): int
    {
        $user = User::whereName($name)->first();

        if ($user) {
            return $user->id;
        }

        $creatorId = DB::table('beatmap_creator_names')->where('name', $name)->value('id');

        if ($creatorId) {
            return $creatorId;
        }

        return -1;
    }

    /**
     * Retrieves a user's ID by name. Does not use the beatmap creator names table as a fallback - only retrieves the
     * ID if the user is a winteright user.
     *
     * @param  string  $name  The user's name.
     * @return int The user's ID if they're a winteright user - otherwise -1.
     */
    public function getIdByNameUsersOnly(string $name): int
    {
        $user = User::whereName($name)->first();

        if ($user) {
            return $user->id;
        }

        return -1;
    }

    /**
     * Updates a user's enabled beatmap modes.
     *
     * @param  int  $userId  The user's ID.
     * @param  bool  $osu  True to enable osu.
     * @param  bool  $taiko  True to enable taiko.
     * @param  bool  $fruits  True to enable fruits.
     * @param  bool  $mania  True to enable mania.
     *
     * @throws Throwable
     */
    public function updateEnabledModes(int $userId, bool $osu, bool $taiko, bool $fruits, bool $mania): void
    {
        $enabledModes = 0;

        if ($osu) {
            $enabledModes |= (1 << 0);
        }     // 0001 or +1
        if ($taiko) {
            $enabledModes |= (1 << 1);
        }   // 0010 or +2
        if ($fruits) {
            $enabledModes |= (1 << 2);
        }  // 0100 or +4
        if ($mania) {
            $enabledModes |= (1 << 3);
        }   // 1000 or +8

        DB::transaction(function () use ($userId, $enabledModes) {
            User::where('id', $userId)->update(['enabled_modes' => $enabledModes]);
        });
    }

    /**
     * Update's a user's privacy settings.
     *
     * @param  int  $userId  The user's ID.
     * @param  HideRatingsOption  $hideRatingsOption  The selected "hide ratings" option.
     * @param  HideCommentsOption  $hideCommentsOption  The selected "hide comments" option.
     *
     * @throws Throwable
     */
    public function updatePrivacySettings(int $userId, HideRatingsOption $hideRatingsOption,
        HideCommentsOption $hideCommentsOption): void
    {
        DB::transaction(function () use ($userId, $hideRatingsOption, $hideCommentsOption) {
            User::where('id', $userId)->update([
                'hide_ratings' => $hideRatingsOption->value,
                'hide_comments' => $hideCommentsOption->value,
            ]);
        });
    }

    /**
     * Updates a user's stored osu! OAuth tokens.
     *
     * @param  int  $userId  The user's ID.
     * @param  string  $accessToken  The user's new access token.
     * @param  string  $refreshToken  The user's new refresh token.
     * @param  Carbon  $expiry  The expiry time for the new tokens.
     *
     * @throws Throwable
     */
    public function updateOsuTokens(int $userId, string $accessToken, string $refreshToken, Carbon $expiry): void
    {
        DB::transaction(function () use ($userId, $accessToken, $refreshToken, $expiry) {
            User::where('id', $userId)->update([
                'osu_access_token' => $accessToken,
                'osu_refresh_token' => $refreshToken,
                'osu_token_expires_at' => $expiry,
            ]);
        });
    }

    /**
     * Retrieves a user's top-rated mappers, calculated using the bayesian average rating that the user has on each
     * mapper's beatmaps.
     *
     * @param  int  $userId  The user's ID.
     * @param  int  $limit  The amount of results to retrieve.
     * @return Collection The user's top-rated mappers.
     */
    public function getTopRatedMappersForUser(int $userId, int $limit = 5): Collection
    {
        $totalRatings = Rating::where('user_id', $userId)->count();
        $averageRating = Rating::where('user_id', $userId)->avg('score') ?? 0;

        return Cache::remember('top_rated_mappers:'.$userId.':'.$limit, 3600, function () use ($userId, $totalRatings, $averageRating, $limit) {
            $results = $this->getTopRatedMappersForUserBase($userId, $totalRatings, $averageRating)
                ->limit($limit)
                ->get();

            return $results->map(function (stdClass $result) {
                return new TopRatedMapper(
                    $result->creator_id,
                    $result->username,
                    $result->creator_name,
                    $result->rating_count,
                    $result->average_score,
                    $result->bayesian
                );
            });
        });
    }

    /**
     * Retrieves and paginates a user's top-rated mappers, calculated using the bayesian average rating that the user
     * has on each mapper's beatmaps.
     *
     * @param  int  $userId  The user's ID.
     * @param  int  $perPage  The amount of results to retrieve per-page.
     * @return LengthAwarePaginator The user's top-rated mappers, paginated.
     */
    public function getTopRatedMappersForUserPaginated(int $userId, int $perPage = 50): LengthAwarePaginator
    {
        $totalRatings = Rating::where('user_id', $userId)->count();
        $averageRating = Rating::where('user_id', $userId)->avg('score') ?? 0;

        return $this->getTopRatedMappersForUserBase($userId, $totalRatings, $averageRating)
            ->paginate($perPage)
            ->through((function (stdClass $result) {
                return new TopRatedMapper(
                    $result->creator_id,
                    $result->username,
                    $result->creator_name,
                    $result->rating_count,
                    $result->average_score,
                    $result->bayesian
                );
            }));
    }

    /**
     * Base query for retrieving a user's top-rated mappers.
     *
     * @param  int  $userId  The user's ID.
     * @param  int  $totalRatings  The user's total rating count.
     * @param  float  $averageRating  The user's average rating across all of their ratings.
     * @return Builder The base query for retrieving a user's top-rated mappers.
     */
    private function getTopRatedMappersForUserBase(int $userId, int $totalRatings, float $averageRating): Builder
    {
        return DB::table('beatmap_creators')
            ->leftJoin('users', 'beatmap_creators.creator_id', '=', 'users.id')
            ->leftJoin('beatmap_creator_names', 'beatmap_creators.creator_id', '=', 'beatmap_creator_names.id')
            ->join('ratings', 'ratings.beatmap_id', '=', 'beatmap_creators.beatmap_id')
            ->where('ratings.user_id', $userId)
            ->select('beatmap_creators.creator_id')
            ->selectRaw('users.name as username')
            ->selectRaw('beatmap_creator_names.name as creator_name')
            ->selectRaw('count(*) as rating_count')
            ->selectRaw('avg(score) as average_score')
            ->selectRaw('(((? * ?) + sum(score)) / (? + count(*))) as bayesian', [$averageRating, $totalRatings, $totalRatings])
            ->groupBy('beatmap_creators.creator_id', 'users.name', 'beatmap_creator_names.name')
            ->orderBy('bayesian', 'desc');
    }
}
