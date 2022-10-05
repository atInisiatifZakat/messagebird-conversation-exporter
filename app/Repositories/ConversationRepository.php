<?php

namespace App\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\HttpClientException;

final class ConversationRepository
{
    private string $key;

    private string $url = 'https://conversations.messagebird.com/v1/conversations';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function fetchCount(): int
    {
        return Http::withToken($this->key, 'AccessKey')->get($this->url)->json('totalCount');
    }

    public function fetchConversations(int $offset): array
    {
        $response = Http::retry(1, 1000, static function (HttpClientException $exception) {
            return $exception instanceof RequestException && $exception->response->clientError();
        })->withToken($this->key, 'AccessKey')->get(\sprintf('%s?offset=%s', $this->url, $offset));

        return Arr::pluck($response->json('items'), 'lastReceivedDatetime', 'id');
    }
}
