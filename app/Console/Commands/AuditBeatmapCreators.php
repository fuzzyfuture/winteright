<?php

namespace App\Console\Commands;

use App\Services\BeatmapService;
use App\Services\BlacklistService;
use App\Services\OsuApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class AuditBeatmapCreators extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beatmaps:audit-creators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if any beatmaps are missing creators';

    protected BeatmapService $beatmapService;
    protected OsuApiService $osuApiService;
    protected BlacklistService $blacklistService;

    public function __construct(BeatmapService $beatmapService, OsuApiService $osuApiService, BlacklistService $blacklistService)
    {
        $this->beatmapService = $beatmapService;
        $this->osuApiService = $osuApiService;
        $this->blacklistService = $blacklistService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Checking for missing creators...');

        $beatmapSets = $this->beatmapService->getBeatmapSetsWithoutCreators();

        $this->info('Found '.$beatmapSets->count()." beatmaps sets with missing creators:");
        $this->newLine();

        foreach ($beatmapSets as $set) {
            $this->info($set->artist.' - '.$set->title);
        }

        $this->newLine();
        if ($this->confirm('Do you want to fix these beatmaps by retrieving their creators?')) {
            $token = $this->osuApiService->getAccessToken();

            foreach ($beatmapSets as $set) {
                $id = $set->id;

                $this->info('Updating '.$set->artist.' - '.$set->title.'...');

                // 1.1-second delay between requests (~55 req/min), per osu! api guidelines. you're welcome peppy
                usleep(1100000);

                try {
                    $fullDetails = Http::withToken($token)->get('https://osu.ppy.sh/api/v2/beatmapsets/'.$id)->json();
                } catch (Throwable $e) {
                    $this->error('Error while attempting to fetch beatmap set with ID '.$id.': '.$e->getMessage());
                    return;
                }

                try {
                    $this->beatmapService->storeBeatmapSetAndBeatmaps($fullDetails, $fullDetails);
                } catch (Throwable $e) {
                    $this->error('Error while updating beatmap set with ID '.$id.': '.$e->getMessage());
                    return;
                }
            }
        }
    }
}
