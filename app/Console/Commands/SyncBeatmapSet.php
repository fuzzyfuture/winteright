<?php

namespace App\Console\Commands;

use App\Services\BeatmapService;
use App\Services\OsuApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class SyncBeatmapSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beatmaps:sync-beatmap-set {setId : The beatmap set ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync a single beatmap set from the osu! API.';

    protected OsuApiService $osuApiService;
    protected BeatmapService $beatmapService;

    public function __construct(OsuApiService $osuApiService, BeatmapService $beatmapService)
    {
        $this->osuApiService = $osuApiService;
        $this->beatmapService = $beatmapService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $setId = $this->argument('setId');

        $this->info('Fetching beatmap set '.$setId.'...');

        $token = $this->osuApiService->getPublicAccessToken();

        try {
            $fullDetails = $this->osuApiService->getBeatmapSetFullDetails($token, $setId);
        } catch (Throwable $e) {
            $this->error('Error while attempting to fetch beatmap set with ID '.$setId.': '.$e->getMessage());
            return;
        }

        try {
            $this->beatmapService->storeBeatmapSetAndBeatmaps($fullDetails, $fullDetails);
        } catch (Throwable $e) {
            $this->error('Error while storing beatmap set with ID '.$setId.': '.$e->getMessage());
            return;
        }

        $this->info('Successfully synced beatmap set '.$fullDetails['artist'].' - '.$fullDetails['title'].'.');
    }
}
