<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

class LoadingSkeleton extends Component
{
    public int $rows = 5;

    public string $type = 'table'; // table | card | list

    public function render(): \Illuminate\View\View
    {
        return view('livewire.loading-skeleton');
    }
}
