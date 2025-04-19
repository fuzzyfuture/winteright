<?php

namespace App\Services;

use App\Models\Beatmap;
use App\Models\BeatmapSet;

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
}
