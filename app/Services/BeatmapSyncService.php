<?php

namespace App\Services;

use App\Models\BeatmapSet;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Throwable;

class BeatmapSyncService
{
    /**
     * Retrieves a beatmap set from the osu! API and stores it in the winteright database. Updates the map if it
     * already exists in the winteright database.
     *
     * @param  int  $setId  The ID of the beatmap set to be synced.
     * @param  ?int  $userId  The ID of the user making the request. Should be null if the sync is requested by
     *                        winteright's backend.
     * @return BeatmapSet The newly synced beatmap set.
     *
     * @throws Throwable
     * @throws ConnectionException
     * @throws AuthenticationException
     */
    public function syncBeatmapSet(int $setId, ?int $userId = null): BeatmapSet
    {
        $osuApiService = app(OsuApiService::class);

        $token = $userId == null ? $osuApiService->getPublicAccessToken() : $osuApiService->getIdentifiedAccessToken($userId);
        $fullDetails = $osuApiService->getBeatmapSetFullDetails($token, $setId);

        $beatmapService = app(BeatmapService::class);

        return $beatmapService->storeBeatmapSetAndBeatmaps($fullDetails, $fullDetails);
    }

    /**
     * Retrieves recently ranked beatmap sets from the osu! API and stores them in the winteright database.
     *
     * @param  ?Carbon  $since  Syncs from the newest ranked beatmap until this date. If unspecified, defaults to the
     *                          date that this method was last called.
     * @param  bool  $skipUpdates  True if syncing existing beatmaps (which updates them) should be skipped. Defaults to
     *                             false.
     * @param  ?callable(string, array, Throwable): void  $onProgress  Callback function for success, error, and skip
     *                                                                 events on individual beatmaps. It should accept
     *                                                                 three parameters: a string for event status
     *                                                                 (success, error, or skip), an array for API set
     *                                                                 data, and an exception which will be null when
     *                                                                 the event status is not "error".
     * @return int The amount of beatmap sets that were synced.
     *
     * @throws ConnectionException
     */
    public function syncRecentlyRankedBeatmapSets(?Carbon $since = null, bool $skipUpdates = false, ?callable $onProgress = null): int
    {
        $siteInfoService = app(SiteInfoService::class);
        $osuApiService = app(OsuApiService::class);
        $beatmapService = app(BeatmapService::class);

        $since = $since ?? $siteInfoService->getLastSyncedRankedBeatmaps();
        $token = $osuApiService->getPublicAccessToken();
        $cursor = null;
        $imported = 0;

        do {
            $data = $osuApiService->searchBeatmapSets($token, 'ranked', 'ranked_desc', true, $cursor);

            foreach ($data['beatmapsets'] ?? [] as $setData) {
                $rankedDate = Carbon::parse($setData['ranked_date']);

                if ($rankedDate <= $since) {
                    break 2;
                }

                if ($skipUpdates && $beatmapService->setExists($setData['id']) && $onProgress) {
                    $onProgress('skip', $setData);
                }

                // 1.1-second delay between requests (~55 req/min), per osu! api guidelines. you're welcome peppy
                usleep(1100000);

                try {
                    $this->syncBeatmapSet($setData['id']);
                    $imported++;

                    if ($onProgress) {
                        $onProgress('success', $setData);
                    }
                } catch (Throwable $e) {
                    if ($onProgress) {
                        $onProgress('error', $setData, $e);
                    }
                }
            }

            $cursor = $data['cursor_string'] ?? null;
        } while ($cursor);

        $siteInfoService->storeLastSyncedRankedBeatmaps(Carbon::now()->toDateTimeString());
        Cache::tags(['recent_beatmap_sets', 'search', 'user_maps'])->flush();

        return $imported;
    }
}
