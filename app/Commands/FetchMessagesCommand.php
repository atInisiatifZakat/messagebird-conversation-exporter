<?php

namespace App\Commands;

use Illuminate\Support\Facades\DB;
use App\Repositories\MessageRepository;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Helper\ProgressBar;

final class FetchMessagesCommand extends Command
{
    protected $signature = 'app:message:export';

    protected $description = 'Fetch and store conversation message in local database';

    public function handle(MessageRepository $messageRepository): int
    {
        $progressBar = new ProgressBar($this->getOutput(), DB::table('conversations')->count('id'));

        DB::table('conversations')->get('conversation_id')->each(function (object $item) use ($messageRepository, $progressBar) {
            $loop = \ceil($messageRepository->fetchCount($item->conversation_id) / 10);

            $offset = 0;

            while ($offset <= $loop) {
                if ($offset % 500 === 0) {
                    sleep(1);
                }

                $messages = $messageRepository->fetchMessage($item->conversation_id, $offset);

                foreach ($messages as $message) {
                    $content = '';

                    if ($message['type'] === 'hsm') {
                        $content = $message['content']['hsm']['templateName'];
                    }

                    if ($message['type'] === 'text') {
                        $content = $message['content']['text'];
                    }

                    DB::table('messages')->updateOrInsert(['message_id' => $message['id']], [
                        'message_id' => $message['id'],
                        'conversation_id' => $message['conversationId'],
                        'platform' => $message['platform'],
                        'to' => $message['to'],
                        'from' => $message['from'],
                        'type' => $message['type'],
                        'content' => $content,
                        'raw' => \json_encode($message),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $offset += 10;
            }

            $progressBar->advance();
        });

        $progressBar->finish();

        return self::SUCCESS;
    }
}
