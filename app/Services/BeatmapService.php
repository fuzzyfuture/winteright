<?php

namespace App\Services;

use App\Models\Beatmap;
use App\Models\BeatmapSet;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     */
    public function storeBeatmapSetAndBeatmaps($setData, $fullDetails): void
    {
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

        foreach ($setData['beatmaps'] as $map) {
            Beatmap::updateOrCreate(
                ['beatmap_id' => $map['id']],
                [
                    'set_id' => $set->set_id,
                    'difficulty_name' => $map['version'],
                    'mode' => $map['mode_int'],
                    'status' => $map['ranked'],
                    'sr' => $map['difficulty_rating'],
                ]
            );
        }
    }

    /**
     * Retrieves recently ranked beatmaps.
     * @param int $limit The amount of recently ranked beatmaps to retrieve. Defaults to 10.
     * @return Collection The recently ranked beatmaps.
     */
    public function getRecentBeatmaps(int $limit = 10): Collection
    {
        return BeatmapSet::withCount('beatmaps')
            ->orderByDesc('date_ranked')
            ->limit($limit)
            ->get();
    }

    /**
     * Retrieves a list of mapper labels for a collection of beatmaps. If the mapper has used OMDB, their username will
     * be displayed - otherwise, just their ID. Used for efficient display of mappers per map on the charts.
     * @param Collection $beatmaps The list of beatmaps to lookup mappers for.
     * @return array The array of creator labels.
     */
    public function getCreatorLabelsForManyBeatmaps(Collection $beatmaps): array
    {
        $beatmapIds = $beatmaps->pluck('beatmap_id')->all();

        $rawCreators = DB::table('beatmap_creators')
            ->whereIn('beatmap_id', $beatmapIds)
            ->get();

        $osuIds = $rawCreators->pluck('creator_id')->unique()->all();
        $users = User::whereIn('osu_id', $osuIds)->get()->keyBy('osu_id');
        $grouped = $rawCreators->groupBy('beatmap_id');

        $result = [];

        foreach ($grouped as $beatmapId => $creators) {
            $result[$beatmapId] = $creators->map(function ($creator) use ($users) {
                $user = $users[$creator->creator_id] ?? null;

                return [
                    'osu_id' => $creator->creator_id,
                    'name' => $user?->name,
                ];
            })->toArray();
        }

        return $result;
    }

    public function addCreator(int $beatmapId, int $creatorId): void
    {
        DB::table('beatmap_creators')->updateOrInsert(
            ['beatmap_id' => $beatmapId, 'creator_id' => $creatorId]
        );
    }
}
