<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Models\Beatmap;
use App\Models\BeatmapSet;
use App\Models\Rating;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class BeatmapService
{
    /**
     * Retrieves a beatmap set with the specified ID. Includes the set owner, the ratings for all difficulties, and
     * the current user's rating for all difficulties. Intended for showing full details of a beatmap set.
     * @param int $setId The ID of the beatmap set to retrieve.
     * @return BeatmapSet The beatmap set.
     */
    public function getBeatmapSet(int $setId): BeatmapSet
    {
        return BeatmapSet::with([
            'creator',
            'beatmaps.ratings',
            'beatmaps.userRating',
            'beatmaps.creators.user',
            'beatmaps.creators.creatorName'
        ])->where('id', $setId)->firstOrFail();
    }

    /**
     * Returns true if a beatmap set with the specified ID exists, false if not.
     *
     * @param int $id The beatmap set ID to check.
     * @return bool True if a beatmap set with the specified ID exists, false if not.
     */
    public function exists(int $id): bool
    {
        return Beatmap::whereId($id)->exists();
    }

    /**
     * Returns true if a beatmap set with the specified ID exists, false if not.
     * @param int $setId The beatmap set ID to check.
     * @return bool True if a beatmap set with the specified ID exists, false if not.
     */
    public function setExists(int $setId): bool
    {
        return BeatmapSet::whereId($setId)->exists();
    }

    /**
     * Adds a creator name to the beatmap_creator_names table. Used to display mapper names for mappers who are not
     * Winteright users.
     * @param int $id The ID of the user.
     * @param string $name The user's name.
     * @throws Throwable
     */
    public function addCreatorName(int $id, string $name): void
    {
        DB::transaction(function () use ($id, $name) {
           DB::table('beatmap_creator_names')->upsert([
               'id' => $id,
               'name' => $name
           ], ['id']);
        });
    }

    /**
     * Retrieves the beatmap's creator name from the beatmap creator names table. Should only be used if it's
     * already known that the beatmap's creator is not a winteright user - otherwise, the name should be pulled from
     * the users table.
     * @param int $id The beatmap creator's ID.
     * @return string The name of the beatmap creator.
     */
    public function getCreatorName(int $id): string
    {
        return DB::table('beatmap_creator_names')->where('id', $id)->value('name') ?? '';
    }

    /**
     * Stores a beatmap (difficulty) in the database. Intended for use while syncing with the osu! API; assumes the
     * parameters are structured as received from the osu! API. Should not be called directly - use
     * `storeBeatmapSetAndBeatmaps()` instead.
     * @param $map
     * @param $setId
     * @param $shouldBlacklist
     * @return void
     * @throws Throwable
     */
    private function storeBeatmap($map, $setId, $shouldBlacklist): void
    {
        DB::transaction(function () use ($map, $setId, $shouldBlacklist) {
            Beatmap::updateOrCreate(
                ['id' => $map['id']],
                [
                    'set_id' => $setId,
                    'difficulty_name' => $map['version'],
                    'mode' => $map['mode_int'],
                    'status' => $map['ranked'],
                    'sr' => $map['difficulty_rating'],
                    'blacklisted' => $shouldBlacklist,
                    'blacklist_reason' => $shouldBlacklist ? 'Mapper requested blacklist.' : null,
                ]
            );
        });
    }

    /**
     * Stores the beatmaps (difficulties) for a particular set in the database. Intended for use while syncing
     * with the osu! API; assumes the parameters are structured as received from the osu! API. Should not be called
     * directly - use `storeBeatmapSetAndBeatmaps()` instead.
     * @param $fullDetails
     * @param $setData
     * @return void
     * @throws Throwable
     */
    private function storeBeatmapsForSet($fullDetails, $setData): void
    {
        $blacklistService = app(BlacklistService::class);
        $userService = app(UserService::class);

        $blacklist = $blacklistService->getBlacklist();
        $existingBeatmapIds = [];

        foreach ($fullDetails['beatmaps'] as $map) {
            $shouldBlacklist = false;
            $creatorIds = [];
            $existingBeatmapIds[] = $map['id'];

            foreach ($map['owners'] as $owner) {
                $creatorIds[] = $owner['id'];

                if (in_array($owner['id'], $blacklist)) {
                    $shouldBlacklist = true;
                }

                if (!$userService->exists($owner['id'])) {
                    $this->addCreatorName($owner['id'], $owner['username']);
                }
            }

            $this->storeBeatmap($map, $setData['id'], $shouldBlacklist);
            $this->setCreators($map['id'], $creatorIds);
        }

        Beatmap::where('set_id', $setData['id'])
            ->whereNotIn('id', $existingBeatmapIds)
            ->delete();
    }

    /**
     * Stores a beatmap set in the database, along with its difficulties (beatmaps). Intended for use while syncing
     * with the osu! API; assumes the parameters are structured as received from the osu! API.
     * @param $setData
     * @param $fullDetails
     * @return void
     * @throws Throwable
     */
    public function storeBeatmapSetAndBeatmaps($setData, $fullDetails): void
    {
        DB::transaction(function () use ($setData, $fullDetails) {
            return BeatmapSet::updateOrCreate(
                ['id' => $setData['id']],
                [
                    'title' => $setData['title'],
                    'artist' => $setData['artist'],
                    'creator_id' => $setData['user_id'],
                    'date_ranked' => $setData['ranked_date'],
                    'has_video' => $fullDetails['video'] ?? false,
                    'has_storyboard' => $setData['storyboard'] ?? false,
                    'genre' => $fullDetails['genre']['id'] ?? null,
                    'lang' => $fullDetails['language']['id'] ?? null,
                ]
            );
        });

        $this->storeBeatmapsForSet($fullDetails, $setData);
    }

    /**
     * Retrieves recently ranked beatmap sets.
     *
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $limit The amount of recently ranked beatmap sets to retrieve. Defaults to 10.
     * @return Collection The recently ranked beatmap sets.
     */
    public function getRecentBeatmapSets(int $enabledModes, int $limit = 10): Collection
    {
        return Cache::tags('recent_beatmap_sets')
            ->remember('beatmap_sets:recent:'.$limit.':'.$enabledModes, 600, function () use ($limit, $enabledModes) {
                $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

                return BeatmapSet::with(['creator', 'creatorName', 'beatmaps'])
                    ->whereHas('beatmaps', function($query) use ($modesArray) {
                        $query->whereIn('mode', $modesArray);
                    })
                    ->orderByDesc('date_ranked')
                    ->limit($limit)
                    ->get();
            });
    }

    /**
     * Adds a beatmap creator entry. Typically used for crediting guest difficulties, although each beatmap should
     * have a creator entry with its mapset's owner, if the difficulty is not a GD.
     * @param int $beatmapId The ID of the beatmap.
     * @param int $creatorId The ID of the creator.
     * @return void
     */
    public function addCreator(int $beatmapId, int $creatorId): void
    {
        DB::table('beatmap_creators')->updateOrInsert(
            ['beatmap_id' => $beatmapId, 'creator_id' => $creatorId]
        );
    }

    /**
     * Adds multiple beatmap creators to a single beatmap in one query.
     * @param int $beatmapId The beatmap ID.
     * @param array $creatorIds The IDs of the creators.
     * @return void
     */
    public function setCreators(int $beatmapId, array $creatorIds): void
    {
        if (empty($creatorIds)) return;

        $rows = array_map(fn($creatorId) => [
            'beatmap_id' => $beatmapId,
            'creator_id' => $creatorId
        ], array_unique($creatorIds));

        DB::table('beatmap_creators')->where('beatmap_id', $beatmapId)->delete();
        DB::table('beatmap_creators')->upsert($rows, ['beatmap_id', 'creator_id']);
    }

    /**
     * Retrieves a list of a user's created beatmaps that are not blacklisted. Used for auditing the blacklist.
     * @param int $id The user's osu! ID.
     * @return Collection The list of beatmaps.
     */
    public function getUnblacklistedForUser(int $id): Collection
    {
        return Beatmap::with('set')
            ->join('beatmap_creators', 'beatmaps.id', '=', 'beatmap_creators.beatmap_id')
            ->where('beatmap_creators.creator_id', $id)
            ->where('beatmaps.blacklisted', false)
            ->select('beatmaps.id', 'beatmaps.difficulty_name', 'beatmaps.set_id')
            ->get();
    }

    /**
     * Marks a list of beatmaps as blacklisted.
     * @param array $beatmapIds The beatmaps to mark as blacklisted.
     * @return void
     */
    public function markAsBlacklisted(array $beatmapIds): void
    {
        Beatmap::whereIn('id', $beatmapIds)
            ->update([
                'blacklisted' => true,
                'blacklist_reason' => 'Mapper requested blacklist.',
            ]);
    }

    /**
     * Retrieves an array of years when beatmaps were ranked.
     * @return Collection An array of years when beatmaps were ranked.
     */
    public function getBeatmapYears(): Collection
    {
        return Cache::remember('beatmap_years', 43200, function () {
            return BeatmapSet::selectRaw('DATE_FORMAT(date_ranked, "%Y") as year')
                ->groupBy('year')
                ->orderByDesc('year')
                ->pluck('year');
        });
    }

    /**
     * Updates the weighted average for the specified beatmap.
     *
     * @param int $id The beatmap's ID.
     * @return void
     * @throws Throwable
     */
    public function updateWeightedAverage(int $id): void
    {
        $newAverage = Rating::selectRaw('AVG(score / 2) as average')
            ->where('beatmap_id', $id)
            ->value('average');

        DB::transaction(function () use ($id, $newAverage) {
            Beatmap::where('id', $id)
                ->update(['weighted_avg' => $newAverage]);
        });
    }

    /**
     * Retrieves all beatmap sets that contain beatmaps which are missing entries in the beatmap_creators table.
     *
     * @return Collection The beatmap sets.
     */
    public function getBeatmapSetsWithoutCreators(): Collection
    {
        return BeatmapSet::whereHas('beatmaps', function ($query) {
            $query->leftJoin('beatmap_creators', 'beatmaps.id', '=', 'beatmap_creators.beatmap_id')
                ->where('beatmap_creators.creator_id', '=', null);
        })->get();
    }

    /**
     * Retrieves the beatmap sets created by a specified user.
     *
     * @param int $userId The user's ID.
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $limit The maximum number of results to return.
     * @return Collection The user's beatmap sets.
     */
    public function getBeatmapSetsForUser(int $userId, int $enabledModes = 15, int $limit = 5): Collection
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return BeatmapSet::where('creator_id', $userId)
            ->whereHas('beatmaps', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray);
            })
            ->with(['creator', 'creatorName', 'beatmaps'])
            ->orderByDesc('date_ranked')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieves and paginates all beatmap sets created by a specified user.
     *
     * @param int $userId The user's ID.
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $perPage The amount of beatmap sets to display per-page.
     * @return LengthAwarePaginator The user's paginated beatmap sets.
     */
    public function getBeatmapSetsForUserPaginated(int $userId, int $enabledModes = 15,
                                                   int $perPage = 50): LengthAwarePaginator
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return BeatmapSet::where('creator_id', $userId)
            ->whereHas('beatmaps', function ($query) use ($modesArray) {
                $query->whereIn('mode', $modesArray);
            })
            ->with(['creator', 'creatorName', 'beatmaps'])
            ->orderByDesc('date_ranked')
            ->paginate($perPage);
    }

    /**
     * Retrieves guest difficulties (beatmaps created on another user's set) created by a specified user.
     *
     * @param int $userId The user's ID.
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $limit The maximum number of results to return.
     * @return Collection The user's recent guest difficulties.
     */
    public function getGuestDifficultiesForUser(int $userId, int $enabledModes = 15, int $limit = 5): Collection
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return Beatmap::whereHas('creators', function ($query) use ($userId) {
                $query->where('creator_id', $userId);
            })
            ->whereHas('set', function ($query) use ($userId) {
                $query->where('creator_id', '!=', $userId);
            })
            ->whereIn('mode', $modesArray)
            ->with(['set', 'creators.user', 'creators.creatorName'])
            ->join('beatmap_sets', 'beatmaps.set_id', '=', 'beatmap_sets.id')
            ->orderByDesc('beatmap_sets.date_ranked')
            ->select('beatmaps.*')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieves and paginates all guest difficulties (beatmaps created on another user's set) created by a specified
     * user.
     *
     * @param int $userId The user's ID.
     * @param int $enabledModes Bitfield of enabled modes.
     * @param int $perPage The amount of guest difficulties to display per page.
     * @return LengthAwarePaginator The paginated guest difficulties.
     */
    public function getGuestDifficultiesForUserPaginated(int $userId, int $enabledModes = 15,
                                                         int $perPage = 50): LengthAwarePaginator
    {
        $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

        return Beatmap::whereHas('creators', function ($query) use ($userId) {
                $query->where('creator_id', $userId);
            })
            ->whereHas('set', function ($query) use ($userId) {
                $query->where('creator_id', '!=', $userId);
            })
            ->whereIn('mode', $modesArray)
            ->with(['set', 'creators.user', 'creators.creatorName'])
            ->join('beatmap_sets', 'beatmaps.set_id', '=', 'beatmap_sets.id')
            ->orderByDesc('beatmap_sets.date_ranked')
            ->select('beatmaps.*')
            ->paginate($perPage);
    }

    /**
     * Retrieves the most recently played beatmaps (last 24 hours) for the specified user.
     *
     * @param int $userId The ID of the user.
     * @return Collection The user's most recently played beatmaps.
     * @throws ConnectionException
     * @throws AuthenticationException
     * @throws Throwable
     */
    public function getRecentlyPlayedForUser(int $userId): Collection
    {
        $osuApiService = app(OsuApiService::class);

        $recentScores = $osuApiService->getUserScores($userId, 'recent');
        $recentBeatmapIds = array_map(fn ($item) => $item['beatmap']['id'], $recentScores);

        return Beatmap::whereIn('id', $recentBeatmapIds)
            ->with(['set', 'creators.user', 'creators.creatorName', 'userRating'])
            ->get()
            ->sortBy(fn ($beatmap) => array_search($beatmap->id, $recentBeatmapIds));
    }

    /**
     * Retrieves the beatmap sets that the user has favorited on the osu! website.
     *
     * @param int $userId The user's ID.
     * @param int $page The current page.
     * @param int $perPage The amount of results to include per-page.
     * @return LengthAwarePaginator The paginated beatmap sets.
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws Throwable
     */
    public function getFavoritesForUserPaginated(int $userId, int $page = 1, int $perPage = 50): LengthAwarePaginator
    {
        $osuApiService = app(OsuApiService::class);

        $offset = ($page - 1) * $perPage;

        $ids = Cache::tags('api:'.$userId)->remember(
            'api:favorites:'.$userId.':'.$page,
            86400,
            function () use ($userId, $osuApiService, $perPage, $offset) {
                $favorites = $osuApiService->getUserBeatmaps($userId, 'favourite', $perPage, $offset);
                return array_map(fn ($item) => $item['id'], $favorites);
            }
        );

        $apiUser = Cache::tags('api:'.$userId)->remember(
            'api:users:'.$userId,
            86400,
            function () use ($userId, $osuApiService) {
                return $osuApiService->getUser($userId);
            }
        );

        $favoriteCount = $apiUser['favourite_beatmapset_count'];

        $beatmaps = BeatmapSet::whereIn('id', $ids)
            ->with(['creator', 'creatorName', 'beatmaps'])
            ->get()
            ->sortBy(fn ($beatmap) => array_search($beatmap->id, $ids));

        return new LengthAwarePaginator(
            $beatmaps,
            $favoriteCount,
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }
}
