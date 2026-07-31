<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_whatsapp')
                ->label('Kirim WA')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->url(fn (): ?string => $this->getRecord()->getWhatsAppUrl())
                ->openUrlInNewTab()
                ->visible(fn (): bool => ! empty($this->getRecord()->customer?->phone)),

            Actions\Action::make('download_pdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(fn (): string => \Illuminate\Support\Facades\URL::signedRoute('invoice.pdf.download', ['invoice' => $this->getRecord()->id]))
                ->openUrlInNewTab(),
        ];
    }
}
