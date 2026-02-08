<?php

namespace App\Console\Commands;

use App\Services\BeatmapSyncService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

class SyncBeatmapSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beatmaps:sync-set {setId : The beatmap set ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync a single beatmap set from the osu! API.';

    protected BeatmapSyncService $beatmapSyncService;

    public function __construct(BeatmapSyncService $beatmapSyncService)
    {
        $this->beatmapSyncService = $beatmapSyncService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $setId = $this->argument('setId');

        $this->info('Fetching beatmap set ' . $setId . '...');

        try {
            $beatmapSet = $this->beatmapSyncService->syncBeatmapSet($setId);
        } catch (AuthenticationException $e) {
            $this->error('Error while attempting to fetch beatmap set with ID ' . $setId . ': ' . $e->getMessage());

            return;
        } catch (ConnectionException $e) {
            $this->error('Error while storing beatmap set with ID ' . $setId . ': ' . $e->getMessage());

            return;
        } catch (Throwable $e) {
            $this->error('Error while attempting to sync beatmap set with ID ' . $setId . ': ' . $e->getMessage());

            return;
        }

        $this->info('Successfully synced beatmap set ' . $beatmapSet->artist . ' - ' . $beatmapSet->title . '.');
    }
}
