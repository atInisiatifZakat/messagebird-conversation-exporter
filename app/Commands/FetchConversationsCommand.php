<?php

namespace App\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use App\Repositories\ConversationRepository;
use Symfony\Component\Console\Helper\ProgressBar;

final class FetchConversationsCommand extends Command
{
    protected $signature = 'app:conversation:export';

    protected $description = 'Fetch and store conversation in local database';

    public function handle(ConversationRepository $conversation): int
    {
        $loop = \ceil($conversation->fetchCount() / 10);

        $progressBar = new ProgressBar($this->getOutput(), $loop);

        $offset = 0;

        while ($offset <= $loop) {
            if ($offset % 500 === 0) {
                sleep(1);
            }

            $conversations = $conversation->fetchConversations($offset);

            foreach ($conversations as $key => $lastUpdate) {
                DB::table('conversations')->updateOrInsert(['conversation_id' => $key], [
                    'conversation_id' => $key,
                    'last_received_datetime' => Carbon::parse($lastUpdate),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $offset += 10;

            $progressBar->advance();
        }

        $progressBar->finish();

        return self::SUCCESS;
    }
}
