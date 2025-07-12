<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SiteInfoService
{
    /**
     * Retrieves the timestamp for the latest time new ranked beatmaps were synced with the osu! API.
     * @return string The timestamp.
     */
    public function getLastSyncedRankedBeatmaps(): string
    {
        return DB::table('site_info')->value('last_synced_ranked_beatmaps');
    }

    /**
     * Stores the timestamp for the latest time new ranked beatmaps were synced with the osu! API.
     * @param string $lastSynced The new timestamp.
     * @return void
     */
    public function storeLastSyncedRankedBeatmaps(string $lastSynced): void
    {
        DB::table('site_info')->updateOrInsert(
            [],
            ['last_synced_ranked_beatmaps' => $lastSynced]
        );
    }
}
