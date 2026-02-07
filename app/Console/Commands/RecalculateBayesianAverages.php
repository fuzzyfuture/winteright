<?php

namespace App\Console\Commands;

use App\Services\ChartsService;
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

    protected ChartsService $chartsService;

    public function __construct(ChartsService $chartsService)
    {
        $this->chartsService = $chartsService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Recalculating Bayesian averages...');

        $this->chartsService->recalculateBayesianAverages();

        $this->info('Bayesian averages updated successfully.');
    }
}
