<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class ToastNotification extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $toasts = [];

    private int $nextId = 0;

    #[On('toast')]
    public function addToast(string $message, string $type = 'info', int $duration = 4000): void
    {
        $this->toasts[] = [
            'id'       => ++$this->nextId,
            'message'  => $message,
            'type'     => $type,
            'duration' => $duration,
        ];
    }

    public function dismiss(int $id): void
    {
        $this->toasts = array_values(
            array_filter($this->toasts, fn ($t) => $t['id'] !== $id)
        );
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.toast-notification');
    }
}
