<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $user,
    ) {
        $this->onQueue('notifications');
    }

    /** @return array<int, string> */
    public function via(User $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Selamat Datang di ' . config('app.name'))
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Akun Anda telah berhasil dibuat.')
            ->line('Silakan login menggunakan email dan password yang telah ditetapkan.')
            ->action('Login Sekarang', url(config('app.url') . '/admin'))
            ->line('Terima kasih telah bergabung bersama kami.');
    }

    /** @return array<string, mixed> */
    public function toArray(User $notifiable): array
    {
        return [
            'message' => 'Selamat datang di ' . config('app.name') . ', ' . $notifiable->name . '!',
            'url'     => url(config('app.url') . '/admin'),
        ];
    }
}
