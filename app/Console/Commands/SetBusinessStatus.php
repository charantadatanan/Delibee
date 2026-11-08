<?php

namespace App\Console\Commands;

use App\Enums\BusinessStatus;
use App\Models\Business;
use Illuminate\Console\Command;

class SetBusinessStatus extends Command
{
    protected $signature = 'business:status
                            {business : The business slug}
                            {status : pending, active or suspended}';

    protected $description = 'Approve, suspend or reset a business';

    public function handle(): int
    {
        $statusValue = $this->argument('status');
        $status = is_string($statusValue) ? BusinessStatus::tryFrom($statusValue) : null;

        if ($status === null) {
            $this->error('Status must be one of: '.implode(', ', BusinessStatus::values()).'.');

            return self::INVALID;
        }

        $business = Business::query()
            ->where('slug', $this->argument('business'))
            ->first();

        if ($business === null) {
            $this->error('No business found with that slug.');

            return self::FAILURE;
        }

        $business->update(['status' => $status]);

        $this->info("{$business->name} is now {$status->label()}.");

        return self::SUCCESS;
    }
}
