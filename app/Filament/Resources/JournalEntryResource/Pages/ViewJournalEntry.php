<?php

declare(strict_types=1);

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewJournalEntry extends ViewRecord
{
    protected static string $resource = JournalEntryResource::class;
}
