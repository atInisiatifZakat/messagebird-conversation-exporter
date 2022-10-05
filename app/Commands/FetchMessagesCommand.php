<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

final class FetchMessagesCommand extends Command
{
    protected $signature = 'app:message:export';

    protected $description = 'Fetch and store conversation message in local database';

    public function handle(): int
    {
        return self::SUCCESS;
    }
}
