<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Exports\InvoicesExport;
use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(new InvoicesExport, 'invoices-piutang-' . now()->format('Ymd') . '.xlsx');
                }),
        ];
    }
}
