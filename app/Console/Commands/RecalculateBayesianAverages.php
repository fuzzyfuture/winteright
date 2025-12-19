<?php

namespace App\Console\Commands;

use App\Models\Beatmap;
use App\Models\Rating;
use App\Services\SiteInfoService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecalculateBayesianAverages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beatmaps:recalculate-bayesian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and update Bayesian averages for all beatmaps.';

    protected SiteInfoService $siteInfoService;

    public function __construct(SiteInfoService $siteInfoService)
    {
        $this->siteInfoService = $siteInfoService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $totalRatings = Rating::count();
        $averageRating = Rating::avg('score') ?? 0;

        if ($totalRatings === 0) {
            $this->warn('No ratings found.');
            return;
        }

        $this->info('Recalculating Bayesian averages...');

        DB::statement("
            UPDATE beatmaps
            INNER JOIN (
                SELECT
                    beatmap_id,
                    COUNT(*) as ratings_count,
                    SUM(score) as total_score
                FROM ratings
                GROUP BY beatmap_id
            ) r ON beatmaps.id = r.beatmap_id
            SET beatmaps.bayesian_avg = ((? * ?) + r.total_score) / (? + r.ratings_count)
        ", [$averageRating, $totalRatings, $totalRatings]);

        $this->siteInfoService->storeLastUpdatedCharts(Carbon::now()->toDateTimeString());

        Cache::tags(['charts'])->flush();

        $this->info('Bayesian averages updated successfully.');
    }
}
