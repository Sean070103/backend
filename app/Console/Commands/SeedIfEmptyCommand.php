<?php

namespace App\Console\Commands;

use App\Models\Protocol;
use Illuminate\Console\Command;

class SeedIfEmptyCommand extends Command
{
    protected $signature = 'seed:if-empty';

    protected $description = 'Run database seed only when no protocols exist (e.g. first deploy)';

    public function handle(): int
    {
        if (Protocol::count() > 0) {
            $this->info('Database already has data. Skipping seed.');
            return self::SUCCESS;
        }

        $this->info('Database empty. Seeding...');
        $this->call('db:seed', ['--force' => true]);
        $this->info('Seed completed.');
        return self::SUCCESS;
    }
}
