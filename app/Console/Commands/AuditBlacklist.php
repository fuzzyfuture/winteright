<?php

namespace App\Console\Commands;

use App\Services\BeatmapService;
use App\Services\BlacklistService;
use Illuminate\Console\Command;

class AuditBlacklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blacklist:audit-blacklist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if blacklisted users have any beatmaps not marked as blacklisted';

    protected BlacklistService $blacklistService;
    protected BeatmapService $beatmapService;

    public function __construct(BlacklistService $blacklistService, BeatmapService $beatmapService)
    {
        $this->blacklistService = $blacklistService;
        $this->beatmapService = $beatmapService;

        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $blacklistedUserIds = $this->blacklistService->GetBlacklist();

        $this->info('Auditing blacklist...');
        $totalIssues = 0;
        $problemBeatmaps = [];

        foreach ($blacklistedUserIds as $userId) {
            $unblacklistedBeatmaps = $this->beatmapService->getUnblacklistedForUser($userId);

            if ($unblacklistedBeatmaps->isNotEmpty()) {
                $problemBeatmaps[$userId] = $unblacklistedBeatmaps;
                $totalIssues += $unblacklistedBeatmaps->count();
            }
        }

        if (empty($problemBeatmaps)) {
            $this->info('All beatmaps by blacklisted users are properly blacklisted.');
            return;
        }

        $this->warn('Found '.$totalIssues.' unblacklisted beatmaps belonging to blacklisted users:');

        foreach ($problemBeatmaps as $userId => $maps) {
            $this->line('User '.$userId.':');
            foreach ($maps as $map) {
                $this->line('   '.$map->set->artist.' - '.$map->set->title.' ['.$map->difficulty_name.']');
                $this->line('   set '.$map->set->set_id.', map '.$map->beatmap_id.', ranked '.$map->set->date_ranked);
                $this->newLine();
            }
        }

        if ($this->confirm('Do you want to fix these beatmaps by marking them as blacklisted?')) {
            $fixCount = 0;

            foreach ($problemBeatmaps as $maps) {
                $beatmapIds = $maps->pluck('beatmap_id')->toArray();
                $this->beatmapService->markAsBlacklisted($beatmapIds);
                $fixCount += count($beatmapIds);
            }

            $this->info('Fixed '.$fixCount.' beatmaps by marking them as blacklisted.');
        } else {
            $this->line('No changes were made.');
        }
    }
}
