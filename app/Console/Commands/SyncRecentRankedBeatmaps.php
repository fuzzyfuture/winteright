<?php

namespace App\Console\Commands;

use App\Services\BeatmapSyncService;
use App\Services\SiteInfoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
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

    protected BeatmapSyncService $beatmapSyncService;
    protected SiteInfoService $siteInfoService;

    public function __construct(BeatmapSyncService $beatmapSyncService, SiteInfoService $siteInfoService)
    {
        $this->siteInfoService = $siteInfoService;
        $this->beatmapSyncService = $beatmapSyncService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $since = $this->option('since') ?
            Carbon::parse($this->option('since')) :
            $this->siteInfoService->getLastSyncedRankedBeatmaps();
        $skipUpdates = $this->option('skip-updates');

        $this->info('syncing ranked beatmaps since ' . $since);

        $imported = 0;

        try {
            $imported = $this->beatmapSyncService->syncRecentlyRankedBeatmapSets(
                $since,
                $skipUpdates,
                function ($status, $setData, $error = null) {
                    match ($status) {
                        'skip' => $this->info('skipped: ' . $setData['artist'] . ' - ' . $setData['title']),
                        'success' => $this->info('imported: ' . $setData['artist'] . ' - ' . $setData['title'] . ', ranked ' . $setData['ranked_date']),
                        'error' => $this->error('error: ' . $setData['id'] . ' - ' . $error->getMessage()),
                    };
                }
            );
        } catch (Throwable $e) {
            $this->error('error while attempting to search for recently ranked beatmap sets: ' . $e->getMessage());
        }

        $this->info('import complete! imported ' . $imported . ' beatmap sets.');
    }
}
