<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneStaleRecords extends Command
{
    protected $signature = 'delibee:prune
                            {--session-days=7 : Delete sessions idle for more than this many days}
                            {--failed-job-days=30 : Delete failed jobs older than this many days}';

    protected $description = 'Prune abandoned sessions and old failed jobs';

    public function handle(): int
    {
        $sessionDays = max(1, (int) $this->option('session-days'));
        $failedJobDays = max(1, (int) $this->option('failed-job-days'));

        $sessions = DB::table('sessions')
            ->where('last_activity', '<', now()->subDays($sessionDays)->getTimestamp())
            ->delete();

        $failedJobs = DB::table('failed_jobs')
            ->where('failed_at', '<', now()->subDays($failedJobDays))
            ->delete();

        $this->info("Pruned {$sessions} stale session(s) and {$failedJobs} failed job(s).");

        return self::SUCCESS;
    }
}
