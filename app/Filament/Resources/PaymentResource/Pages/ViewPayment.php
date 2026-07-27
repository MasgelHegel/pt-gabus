<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource;
use App\Models\Account;
use App\Models\Payment;
use App\Services\OrderWorkflowService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // Buka bukti di tab baru
            Actions\Action::make('open_proof')
                ->label('Buka Bukti Transfer')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->visible(fn (): bool => (bool) $this->record->proof_file)
                ->url(fn (): string => $this->record->proof_url ?? asset('storage/' . $this->record->proof_file))
                ->openUrlInNewTab(),

            // Verifikasi
            Actions\Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === PaymentStatus::Pending)
                ->form([
                    Forms\Components\Select::make('account_id')
                        ->label('Akun Kas/Bank Penerima')
                        ->options(fn () => Account::where('is_cash_bank', true)
                            ->orderBy('code')->pluck('name', 'id'))
                        ->required()
                        ->native(false),
                    Forms\Components\Placeholder::make('info')
                        ->label('')
                        ->content('Invoice → Lunas, Piutang berkurang, Jurnal otomatis dibuat.'),
                ])
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Pembayaran')
                ->action(function (array $data, OrderWorkflowService $service): void {
                    $service->verifyPayment(
                        payment:         $this->record,
                        isApproved:      true,
                        verifiedBy:      (int) auth()->id(),
                        accountId:       (int) $data['account_id'],
                        rejectionReason: null,
                    );

                    Notification::make()
                        ->title('Pembayaran Diverifikasi')
                        ->body('Invoice lunas, piutang berkurang, jurnal dibuat otomatis.')
                        ->success()->send();

                    $this->refreshFormData(['status', 'verified_by', 'verified_at']);
                }),

            // Tolak
            Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->status === PaymentStatus::Pending)
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->required()->rows(3)
                        ->placeholder('Contoh: Nominal tidak sesuai, bukti tidak jelas...'),
                ])
                ->action(function (array $data, OrderWorkflowService $service): void {
                    $service->verifyPayment(
                        payment:         $this->record,
                        isApproved:      false,
                        verifiedBy:      (int) auth()->id(),
                        accountId:       null,
                        rejectionReason: $data['rejection_reason'],
                    );

                    Notification::make()
                        ->title('Pembayaran Ditolak')
                        ->warning()->send();

                    $this->refreshFormData(['status', 'rejection_reason']);
                }),
        ];
    }
}
