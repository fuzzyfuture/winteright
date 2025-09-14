<?php

namespace App\Services;

use App\Enums\BeatmapMode;
use App\Models\Beatmap;
use App\Models\BeatmapSet;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
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
     * Stores a beatmap set in the database, along with its difficulties (beatmaps). Intended for use while syncing
     * with the osu! API; assumes the parameters are structured as received from the osu! API.
     * @param $setData
     * @param $fullDetails
     * @return void
     * @throws Throwable
     */
    public function storeBeatmapSetAndBeatmaps($setData, $fullDetails): void
    {
        $blacklistService = app(BlacklistService::class);
        $blacklist = $blacklistService->getBlacklist();

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

        $userService = app(UserService::class);

        foreach ($fullDetails['beatmaps'] as $map) {
            $shouldBlacklist = false;
            $creatorIds = [];

            foreach ($map['owners'] as $owner) {
                $creatorIds[] = $owner['id'];

                if (in_array($owner['id'], $blacklist)) {
                    $shouldBlacklist = true;
                }

                if (!$userService->exists($owner['id'])) {
                    $this->addCreatorName($owner['id'], $owner['username']);
                }
            }

            DB::transaction(function () use ($map, $setData, $shouldBlacklist) {
                Beatmap::updateOrCreate(
                    ['id' => $map['id']],
                    [
                        'set_id' => $setData['id'],
                        'difficulty_name' => $map['version'],
                        'mode' => $map['mode_int'],
                        'status' => $map['ranked'],
                        'sr' => $map['difficulty_rating'],
                        'blacklisted' => $shouldBlacklist,
                        'blacklist_reason' => $shouldBlacklist ? 'Mapper requested blacklist.' : null,
                    ]
                );
            });

            $this->addCreators($map['id'], $creatorIds);
        }
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
        return Cache::remember('recent_'.$limit.'_beatmap_sets_'.$enabledModes, 43200, function () use ($limit, $enabledModes) {
            $modesArray = BeatmapMode::bitfieldToArray($enabledModes);

            return BeatmapSet::withCount('beatmaps')
                ->with('creator')
                ->whereHas('beatmaps', function($query) use ($modesArray) {
                    $query->whereIn('mode', $modesArray);
                })
                ->orderByDesc('date_ranked')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Returns the raw creator data (beatmap ID, user ID) for a list of beatmap IDs.
     * @param array $beatmapIds The list of beatmap IDs.
     * @return Collection The raw creator data (beatmap ID, user ID)
     */
    public function getRawCreators(array $beatmapIds): Collection
    {
        return DB::table('beatmap_creators')
            ->whereIn('beatmap_id', $beatmapIds)
            ->get()
            ->unique();
    }

    /**
     * Applies creator labels to each beatmap in a collection. If the mapper has used OMDB or winteright, their
     * username will be linked and displayed. If they are present in the beatmap creators table, their unlinked
     * username will be displayed. Otherwise, their ID is displayed. See `applyCreatorLabelsToSets()` for a similar
     * method for beatmap sets.
     * @param Collection $beatmaps The list of beatmaps to lookup mappers for.
     * @return void
     */
    public function applyCreatorLabels(Collection $beatmaps): void
    {
        $beatmapIds = $beatmaps->pluck('id')->all();

        $rawCreators = $this->getRawCreators($beatmapIds);
        $grouped = $rawCreators->groupBy('beatmap_id');

        $ids = $rawCreators->pluck('creator_id')->unique()->all();
        $users = User::whereIn('id', $ids)->get()->keyBy('id');
        $names = DB::table('beatmap_creator_names')->whereIn('id', $ids)->get()->keyBy('id');

        foreach ($grouped as $beatmapId => $creators) {
            $labels = $creators->map(function ($creator) use ($users, $names) {
                return $this->resolveLabel($creator->creator_id, $users, $names);
            })->toArray();

            $beatmap = $beatmaps->firstWhere('id', $beatmapId);
            $beatmap->setExternalCreatorLabels($labels);
        }
    }

    /**
     * Applies creator labels to each beatmap set in a collection. If the mapper has used OMDB or winteright, their
     * username will be linked and displayed. If they are present in the beatmap creators table, their unlinked
     * username will be displayed. Otherwise, their ID is displayed. See `applyCreatorLabels()` for a similar method
     * for beatmaps.
     * @param Collection $beatmapSets The list of beatmap sets to lookup mappers for.
     * @return void
     */
    public function applyCreatorLabelsToSets(Collection $beatmapSets): void
    {
        $ids = $beatmapSets->pluck('creator_id')->all();

        $users = User::whereIn('id', $ids)->get()->keyBy('id');
        $names = DB::table('beatmap_creator_names')->whereIn('id', $ids)->get()->keyBy('id');

        foreach ($beatmapSets as $beatmapSet) {
            $label = $this->resolveLabel($beatmapSet->creator_id, $users, $names);
            $beatmapSet->setExternalCreatorLabel($label);
        }
    }

    /**
     * Creates a label array for a beatmap or beatmap set's creator, given the creator's ID and the prefetched
     * collections of users and creator names. Used when mass-applying creator labels to collections of beatmaps and
     * beatmap sets.
     * @param int $creatorId The creator's ID.
     * @param Collection $users The prefetched collection of users.
     * @param Collection $names The prefetched collection of names.
     * @return array The label.
     */
    protected function resolveLabel(int $creatorId, Collection $users, Collection $names): array
    {
        $winterightName = $users[$creatorId]?->name ?? '';
        $creatorName = $names[$creatorId]?->name ?? '';

        if (!blank($winterightName)) {
            $name = $winterightName;
            $isWinteright = true;
        } else if (!blank($creatorName)) {
            $name = $creatorName;
            $isWinteright = false;
        } else {
            $name = '';
            $isWinteright = false;
        }

        return [
            'id' => $creatorId,
            'name' => $name,
            'isWinteright' => $isWinteright,
        ];
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
    public function addCreators(int $beatmapId, array $creatorIds): void
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
     *
     * @param $id
     * @return void
     * @throws Throwable
     */
    public function updateWeightedAverage($id): void
    {
        $newAverage = Rating::selectRaw('AVG(score / 2) as average')
            ->where('beatmap_id', $id)
            ->value('average');

        DB::transaction(function () use ($id, $newAverage) {
            Beatmap::where('id', $id)
                ->update(['weighted_avg' => $newAverage]);
        });
    }
}
