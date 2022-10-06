<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\MessageRepository;
use App\Repositories\ConversationRepository;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        $this->app->singleton(ConversationRepository::class, fn() => new ConversationRepository(
            env('MESSAGEBIRD_ACCESS_KEY')
        ));

        $this->app->singleton(MessageRepository::class, fn() => new MessageRepository(
            env('MESSAGEBIRD_ACCESS_KEY')
        ));
    }
}
