<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Enums\UserRole;
use App\Exports\CustomersExport;
use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn () => auth()->user()?->hasAnyRole([UserRole::SuperAdmin->value, UserRole::Admin->value]))
                ->action(function () {
                    $query = $this->getFilteredTableQuery()
                        ? (clone $this->getFilteredTableQuery())
                        : Customer::with('user');

                    return Excel::download(
                        new CustomersExport($query),
                        'data-customer-' . now()->format('Ymd') . '.xlsx'
                    );
                }),
            Actions\CreateAction::make(),
        ];
    }
}

