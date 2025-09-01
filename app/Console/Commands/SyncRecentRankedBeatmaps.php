<?php

namespace App\Console\Commands;

use App\Services\BeatmapService;
use App\Services\OsuApiService;
use App\Services\SiteInfoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class SyncRecentRankedBeatmaps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beatmaps:sync-ranked {--since= : Override the last synced date (YYYY-MM-DD)} {--skip-updates : Skip updating existing beatmap sets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the most recently ranked beatmaps.';

    protected OsuApiService $osuApiService;
    protected SiteInfoService $siteInfoService;
    protected BeatmapService $beatmapService;

    public function __construct(OsuApiService $osuApiService,
                                SiteInfoService $siteInfoService,
                                BeatmapService $beatmapService)
    {
        $this->osuApiService = $osuApiService;
        $this->siteInfoService = $siteInfoService;
        $this->beatmapService = $beatmapService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $since = $this->option('since');
        $skipUpdates = $this->option('skip-updates');

        $lastSynced = $since
            ? Carbon::parse($since)->toDateTimeString()
            : $this->siteInfoService->getLastSyncedRankedBeatmaps();

        $this->info('Last synced: '.$lastSynced);

        $newestRanked = Carbon::parse($lastSynced);
        $token = $this->osuApiService->getAccessToken();
        $cursor = null;
        $imported = 0;

        do {
            try {
                $response = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/beatmapsets/search', [
                    'status' => 'ranked',
                    'cursor_string' => $cursor,
                    'sort' => 'ranked_desc',
                    'nsfw' => 'true'
                ]);
            } catch (Throwable $e) {
                $this->error('Error while retrieving latest ranked beatmaps at position '.$cursor.': '.$e->getMessage());
                continue;
            }

            $data = $response->json();

            foreach ($data['beatmapsets'] ?? [] as $setData) {
                $rankedDate = $setData['ranked_date'];

                $this->info('Importing: '.$setData['artist'].' - '.$setData['title'].', ranked: '.$rankedDate.'...');

                if (Carbon::parse($rankedDate) <= $newestRanked) {
                    break 2;
                }

                if ($skipUpdates && $this->beatmapService->setExists($setData['id'])) {
                    $this->info('Skipped.');
                    continue;
                }

                // 1.1-second delay between requests (~55 req/min), per osu! api guidelines. you're welcome peppy
                usleep(1100000);

                $fullDetails = null;

                try {
                    $fullDetails = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/beatmapsets/'.$setData['id'])->json();
                } catch (Throwable $e) {
                    $this->error('Error while retrieving details for beatmap set '.$setData['id'].': '.$e->getMessage());
                    continue;
                }

                try {
                    $this->beatmapService->storeBeatmapSetAndBeatmaps($setData, $fullDetails);
                } catch (Throwable $e) {
                    $this->error('Error while storing beatmap set '.$setData['id'].': '.$e->getMessage());
                    continue;
                }

                $imported++;
                $this->info('Done.');
            }

            $cursor = $data['cursor_string'] ?? null;
        } while ($cursor);

        $this->siteInfoService->storeLastSyncedRankedBeatmaps(Carbon::now()->toDateTimeString());

        Cache::tags(['recent_beatmap_sets', 'search'])->flush();

        $this->info('Import complete! Imported '.$imported.' beatmap sets.');
    }
}
