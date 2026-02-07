<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SiteInfoService
{
    /**
     * Retrieves the timestamp for the latest time new ranked beatmaps were synced with the osu! API.
     *
     * @return ?Carbon The timestamp.
     */
    public function getLastSyncedRankedBeatmaps(): ?Carbon
    {
        return Carbon::parse(DB::table('site_info')->value('last_synced_ranked_beatmaps'));
    }

    /**
     * Stores the timestamp for the latest time new ranked beatmaps were synced with the osu! API.
     *
     * @param  string  $lastSynced  The new timestamp.
     */
    public function storeLastSyncedRankedBeatmaps(string $lastSynced): void
    {
        DB::table('site_info')->updateOrInsert(
            [],
            ['last_synced_ranked_beatmaps' => $lastSynced]
        );
    }

    /**
     * Retrieves the timestamp for the latest time the charts were updated (bayesian averages were recalculated).
     *
     * @return ?Carbon The timestamp.
     */
    public function getLastUpdatedCharts(): ?Carbon
    {
        return Carbon::parse(DB::table('site_info')->value('last_updated_charts'));
    }

    /**
     * Stores the timestamp for the latest time the charts were updated (bayesian averages were recalculated).
     *
     * @param  string  $lastUpdated  The new timestamp.
     */
    public function storeLastUpdatedCharts(string $lastUpdated): void
    {
        DB::table('site_info')->updateOrInsert(
            [],
            ['last_updated_charts' => $lastUpdated]
        );
    }
}
