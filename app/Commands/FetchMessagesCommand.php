<?php

namespace App\Commands;

use Illuminate\Support\Arr;
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

                    if ($message['type'] === 'hsm' && \array_key_exists('hsm', $message['content'])) {
                        $content = $message['content']['hsm']['templateName'];
                    }

                    if ($message['type'] === 'text' && \array_key_exists('text', $message['content'])) {
                        $content = $message['content']['text'];
                    }

                    $values = Arr::only($message, ['platform', 'to', 'from', 'type']);

                    DB::table('messages')->updateOrInsert(['message_id' => $message['id']], \array_merge($values, [
                        'message_id' => $message['id'],
                        'conversation_id' => $message['conversationId'],
                        'content' => $content,
                        'raw' => \json_encode($message),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }

                $offset += 10;
            }

            $progressBar->advance();
        });

        $progressBar->finish();

        return self::SUCCESS;
    }
}
