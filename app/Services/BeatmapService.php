<?php

namespace App\Services;

use App\Models\Beatmap;
use App\Models\BeatmapSet;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class BeatmapService
{
    protected BlacklistService $blacklistService;

    public function __construct(BlacklistService $blacklistService)
    {
        $this->blacklistService = $blacklistService;
    }

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
        ])->where('set_id', $setId)->firstOrFail();
    }

    /**
     * Returns true if a beatmap set with the specified ID exists, false if not.
     * @param int $setId The beatmap set ID to check.
     * @return bool True if a beatmap set with the specified ID exists, false if not.
     */
    public function beatmapSetExists(int $setId): bool
    {
        return BeatmapSet::where('set_id', $setId)->exists();
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
            $blacklist = $this->blacklistService->getBlacklist();
            $set = BeatmapSet::updateOrCreate(
                ['set_id' => $setData['id']],
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

            foreach ($fullDetails['beatmaps'] as $map) {
                $shouldBlacklist = false;
                $creatorIds = [];

                foreach ($map['owners'] as $owner) {
                    $creatorIds[] = $owner['id'];

                    if (in_array($owner['id'], $blacklist)) {
                        $shouldBlacklist = true;
                    }
                }

                Beatmap::updateOrCreate(
                    ['beatmap_id' => $map['id']],
                    [
                        'set_id' => $set->set_id,
                        'difficulty_name' => $map['version'],
                        'mode' => $map['mode_int'],
                        'status' => $map['ranked'],
                        'sr' => $map['difficulty_rating'],
                        'blacklisted' => $shouldBlacklist,
                        'blacklist_reason' => $shouldBlacklist ? 'Mapper requested blacklist.' : null,
                    ]
                );

                $this->addCreators($map['id'], $creatorIds);
            }
        });
    }

    /**
     * Retrieves recently ranked beatmap sets.
     * @param int $limit The amount of recently ranked beatmap sets to retrieve. Defaults to 10.
     * @return Collection The recently ranked beatmap sets.
     */
    public function getRecentBeatmapSets(int $limit = 10): Collection
    {
        return Cache::remember('recent_'.$limit.'_beatmap_sets', 43200, function () use ($limit) {
            return BeatmapSet::withCount('beatmaps')
                ->with('creator')
                ->orderByDesc('date_ranked')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Applies creator labels to each beatmap in a collection. If the mapper has used OMDB, their username will
     * be displayed - otherwise, just their ID. Used for efficient display of mappers per map on the charts.
     * @param Collection $beatmaps The list of beatmaps to lookup mappers for.
     */
    public function applyCreatorLabels(Collection $beatmaps): void
    {
        $beatmapIds = $beatmaps->pluck('beatmap_id')->all();

        $rawCreators = DB::table('beatmap_creators')
            ->whereIn('beatmap_id', $beatmapIds)
            ->get();

        $ids = $rawCreators->pluck('creator_id')->unique()->all();
        $users = User::whereIn('id', $ids)->get()->keyBy('id');
        $grouped = $rawCreators->groupBy('beatmap_id');

        foreach ($grouped as $beatmapId => $creators) {
            $labels = $creators->map(function ($creator) use ($users) {
                $user = $users[$creator->creator_id] ?? null;

                return [
                    'id' => $creator->creator_id,
                    'name' => $user?->name,
                ];
            })->toArray();

            $beatmap = $beatmaps->firstWhere('beatmap_id', $beatmapId);
            $beatmap->setExternalCreatorLabels($labels);
        }
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
            ->join('beatmap_creators', 'beatmaps.beatmap_id', '=', 'beatmap_creators.beatmap_id')
            ->where('beatmap_creators.creator_id', $id)
            ->where('beatmaps.blacklisted', false)
            ->select('beatmaps.beatmap_id', 'beatmaps.difficulty_name', 'beatmaps.set_id')
            ->get();
    }

    /**
     * Marks a list of beatmaps as blacklisted.
     * @param array $beatmapIds The beatmaps to mark as blacklisted.
     * @return void
     */
    public function markAsBlacklisted(array $beatmapIds): void
    {
        Beatmap::whereIn('beatmap_id', $beatmapIds)
            ->update([
                'blacklisted' => true,
                'blacklist_reason' => 'Mapper requested blacklist.',
            ]);
    }
}
