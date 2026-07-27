<?php

declare(strict_types=1);

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Enums\UserRole;
use Filament\Resources\Pages\CreateRecord;

class CreateSales extends CreateRecord
{
    protected static string $resource = SalesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Sales berhasil dibuat';
    }

    protected function afterCreate(): void
    {
        $this->getRecord()->assignRole(UserRole::Sales->value);
    }
}
