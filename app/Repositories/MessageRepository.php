<?php

namespace App\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\HttpClientException;

final class MessageRepository
{
    private string $key;

    private string $url = 'https://conversations.messagebird.com/v1/conversations';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function fetchCount(string $conversationId): int
    {
        $url = \sprintf('%s/%s/messages', $this->url, $conversationId);

        return Http::withToken($this->key, 'AccessKey')->get($url)->json('totalCount');
    }

    public function fetchMessage(string $conversationId, int $offset): array
    {
        $url = \sprintf('%s/%s/messages', $this->url, $conversationId);

        $response = Http::retry(1, 1000, static function (HttpClientException $exception) {
            return $exception instanceof RequestException && $exception->response->clientError();
        })->withToken($this->key, 'AccessKey')->get(\sprintf('%s?offset=%s', $url, $offset));

        return $response->json('items');
    }
}
