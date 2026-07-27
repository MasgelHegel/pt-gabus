<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserCreated;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'notifications';

    public int $tries = 3;

    public int $backoff = 60;

    public function handle(UserCreated $event): void
    {
        $event->user->notify(new WelcomeNotification($event->user));
    }

    public function failed(UserCreated $event, \Throwable $exception): void
    {
        logger()->error('SendWelcomeNotification failed', [
            'user_id' => $event->user->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
