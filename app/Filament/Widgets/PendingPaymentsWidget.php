<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingPaymentsWidget extends BaseWidget
{
    protected static ?string $heading    = 'Bukti Bayar Menunggu Verifikasi';
    protected static ?int    $sort       = 4;
    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = '10s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()
                    ->with(['customer', 'invoice'])
                    ->where('status', PaymentStatus::Pending)
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->weight(\Filament\Support\Enums\FontWeight::Medium),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('Invoice'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->color('success'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tgl Bayar')
                    ->date('d M Y'),
            ])
            ->actions([
                Actions\Action::make('view_proof')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Payment $r): string => $r->proof_url ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn (Payment $r): bool => (bool) $r->proof_file),

                Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Payment $record): void {
                        $account = \App\Models\Account::where('is_cash_bank', true)->first();
                        if (! $account) {
                            Notification::make()->title('Akun kas/bank belum diset')->warning()->send();
                            return;
                        }
                        app(\App\Actions\Payment\VerifyPaymentAction::class)($record->id, $account->id);
                        Notification::make()->title('Pembayaran diverifikasi')->success()->send();
                    }),
            ])
            ->paginated(false);
    }
}
