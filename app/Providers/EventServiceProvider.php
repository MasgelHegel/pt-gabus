<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\UserCreated;
use App\Events\UserUpdated;
use App\Listeners\SendWelcomeNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /** @var array<class-string, array<int, class-string>> */
    protected $listen = [
        UserCreated::class => [
            SendWelcomeNotification::class,
        ],

        UserUpdated::class => [
            // Add listeners here as needed
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
