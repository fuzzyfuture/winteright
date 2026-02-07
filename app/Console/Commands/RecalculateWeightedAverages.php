<?php

namespace App\Console\Commands;

use App\Models\Beatmap;
use App\Services\BeatmapService;
use Illuminate\Console\Command;
use Throwable;

class RecalculateWeightedAverages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beatmaps:recalculate-weighted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and update weighted averages for all beatmaps.';

    protected BeatmapService $beatmapService;

    public function __construct(BeatmapService $beatmapService)
    {
        $this->beatmapService = $beatmapService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating weighted averages...');

        Beatmap::chunkById(10000, function ($beatmaps) {
            foreach ($beatmaps as $beatmap) {
                try {
                    $this->beatmapService->updateWeightedAverage($beatmap->id);
                } catch (Throwable $e) {
                    $this->error('Error recalculating for ' . $beatmap->id . ': ' . $e->getMessage());
                }
            }
        });

        $this->info('Weighted averages updated successfully.');
    }
}
