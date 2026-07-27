<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Exports\PaymentsExport;
use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(new PaymentsExport, 'verifikasi-pembayaran-' . now()->format('Ymd') . '.xlsx');
                }),
        ];
    }
}
