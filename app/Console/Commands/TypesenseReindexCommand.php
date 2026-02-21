<?php

namespace App\Console\Commands;

use App\Services\TypesenseService;
use Illuminate\Console\Command;

class TypesenseReindexCommand extends Command
{
    protected $signature = 'typesense:reindex';

    protected $description = 'Reindex all protocols and threads into Typesense';

    public function handle(TypesenseService $typesense): int
    {
        if (! $typesense->enabled()) {
            $this->error('Typesense is not enabled. Set TYPESENSE_ENABLED=true and configure host/api_key in .env');

            return self::FAILURE;
        }

        $this->info('Reindexing...');

        $result = $typesense->reindexAll();

        $this->info($result['message']);
        $this->table(
            ['Collection', 'Count'],
            [
                ['protocols', $result['protocols']],
                ['threads', $result['threads']],
            ]
        );

        return self::SUCCESS;
    }
}
