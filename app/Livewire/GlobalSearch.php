<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public bool $isOpen = false;

    #[Computed]
    public function results(): Collection
    {
        if (strlen($this->query) < 2) {
            return collect();
        }

        $users = User::query()
            ->where(function ($q): void {
                $q->where('name', 'like', "%{$this->query}%")
                  ->orWhere('email', 'like', "%{$this->query}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'email', 'avatar']);

        return collect([
            'users' => [
                'label'   => 'Pengguna',
                'icon'    => 'heroicon-o-users',
                'results' => $users->map(fn ($u) => [
                    'id'       => $u->id,
                    'title'    => $u->name,
                    'subtitle' => $u->email,
                    'url'      => route('filament.admin.resources.users.edit', $u->id),
                ]),
            ],
        ]);
    }

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query  = '';
    }

    #[On('keydown.escape')]
    public function handleEscape(): void
    {
        $this->close();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.global-search');
    }
}
