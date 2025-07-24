<?php

namespace App\Console\Commands;

use App\Models\Beatmap;
use App\Models\Rating;
use Illuminate\Console\Command;

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

        Beatmap::withSum('ratings', 'score')
            ->chunkById(10000, function ($beatmaps) use ($totalRatings, $averageRating) {
                foreach ($beatmaps as $beatmap) {
                    $ratingsCount = $beatmap->rating_count ?? 0;
                    $totalScore = $beatmap->ratings_sum_score ?? 0;

                    if ($ratingsCount === 0) continue;

                    $bayesian = (($averageRating * $totalRatings) + $totalScore) / ($totalRatings + $ratingsCount);

                    $beatmap->update([
                        'bayesian_avg' => $bayesian,
                    ]);
                }
            });

        $this->info('Bayesian averages updated successfully.');
    }
}
