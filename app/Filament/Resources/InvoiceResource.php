<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\OrderWorkflowService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Keuangan & Kas';
    protected static ?string $navigationLabel  = 'Invoice & Piutang';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'Invoice';
    protected static ?string $pluralModelLabel = 'Invoice';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Detail Invoice')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')->label('No. Invoice')->disabled(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')->label('Customer')->disabled(),
                    Forms\Components\DatePicker::make('invoice_date')->label('Tgl Invoice')->disabled(),
                    Forms\Components\DatePicker::make('due_date')->label('Jatuh Tempo')->disabled(),
                    Forms\Components\TextInput::make('subtotal')->label('Subtotal')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('total_amount')->label('Total Tagihan')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('gasback')->label('Gasback')->prefix('Rp')->disabled(),
                    Forms\Components\Select::make('status')
                        ->options(InvoiceStatus::options())
                        ->native(false)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Tgl Invoice')
                    ->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')->sortable()
                    ->color(fn (Invoice $r): string =>
                        $r->due_date->isPast() && ! in_array($r->status->value, ['paid', 'cancelled'])
                            ? 'danger' : 'gray'
                    ),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Tagihan')
                    ->money('IDR')->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (InvoiceStatus $state): string => $state->color())
                    ->formatStateUsing(fn (InvoiceStatus $state): string => $state->label())
                    ->sortable(),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(InvoiceStatus::options())
                    ->native(false),

                Tables\Filters\Filter::make('overdue')
                    ->label('Jatuh Tempo')
                    ->query(fn ($q) => $q
                        ->whereNotIn('status', ['paid', 'cancelled'])
                        ->where('due_date', '<', now())),

                Tables\Filters\Filter::make('needs_verification')
                    ->label('Butuh Verifikasi')
                    ->query(fn ($q) => $q->where('status', 'payment_uploaded')),
            ])
            ->actions([

                // ── LIHAT BUKTI BAYAR ─────────────────────────────────────
                Actions\Action::make('view_proof')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->visible(fn (Invoice $r): bool =>
                        $r->status === InvoiceStatus::PaymentUploaded
                    )
                    ->url(function (Invoice $r): ?string {
                        $payment = $r->payments()
                            ->where('status', PaymentStatus::Pending->value)
                            ->latest()->first();

                        return $payment?->proof_url;
                    })
                    ->openUrlInNewTab(),

                // ── VERIFIKASI PEMBAYARAN ─────────────────────────────────
                Actions\Action::make('verify_payment')
                    ->label('✓ Verifikasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Invoice $r): bool =>
                        $r->status === InvoiceStatus::PaymentUploaded
                    )
                    ->modalHeading(fn (Invoice $r): string => "Verifikasi Pembayaran — {$r->invoice_number}")
                    ->modalDescription(fn (Invoice $r): string =>
                        "Total tagihan: Rp " . number_format((float) $r->total_amount, 0, ',', '.')
                    )
                    ->form([
                        Forms\Components\Select::make('account_id')
                            ->label('Akun Kas/Bank Penerima')
                            ->options(fn () => Account::where('is_cash_bank', true)
                                ->orderBy('code')
                                ->pluck('name', 'id'))
                            ->required()
                            ->native(false)
                            ->helperText('Pilih rekening tempat dana pembayaran masuk'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (Invoice $record, array $data, OrderWorkflowService $service): void {
                        /** @var Payment|null $payment */
                        $payment = $record->payments()
                            ->where('status', PaymentStatus::Pending->value)
                            ->latest()->first();

                        if (! $payment) {
                            Notification::make()
                                ->title('Tidak ada bukti bayar pending')
                                ->warning()->send();
                            return;
                        }

                        $service->verifyPayment(
                            payment:         $payment,
                            isApproved:      true,
                            verifiedBy:      (int) auth()->id(),
                            accountId:       (int) $data['account_id'],
                            rejectionReason: null,
                        );

                        Notification::make()
                            ->title('✅ Pembayaran Diverifikasi!')
                            ->body("Invoice {$record->invoice_number} sekarang Lunas. Piutang & jurnal diperbarui otomatis.")
                            ->success()->send();
                    }),

                // ── TOLAK PEMBAYARAN ──────────────────────────────────────
                Actions\Action::make('reject_payment')
                    ->label('✗ Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Invoice $r): bool =>
                        $r->status === InvoiceStatus::PaymentUploaded
                    )
                    ->modalHeading('Tolak Pembayaran')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Contoh: Nominal tidak sesuai, Bukti tidak jelas, dsb.'),
                    ])
                    ->action(function (Invoice $record, array $data, OrderWorkflowService $service): void {
                        /** @var Payment|null $payment */
                        $payment = $record->payments()
                            ->where('status', PaymentStatus::Pending->value)
                            ->latest()->first();

                        if ($payment) {
                            $service->verifyPayment(
                                payment:         $payment,
                                isApproved:      false,
                                verifiedBy:      (int) auth()->id(),
                                accountId:       null,
                                rejectionReason: $data['rejection_reason'],
                            );
                        }

                        Notification::make()
                            ->title('Pembayaran Ditolak')
                            ->body('Invoice dikembalikan ke status Belum Dibayar. Customer perlu upload ulang.')
                            ->warning()->send();
                    }),

                // ── CATAT BAYAR MANUAL (admin input langsung tanpa upload) ─
                Actions\Action::make('record_payment')
                    ->label('Catat Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->visible(fn (Invoice $r): bool =>
                        in_array($r->status->value, ['unpaid', 'overdue'])
                    )
                    ->modalHeading(fn (Invoice $r): string => "Catat Pembayaran Manual — {$r->invoice_number}")
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal Pembayaran')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->default(fn (Invoice $r) => (float) $r->total_amount),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Bayar')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\Select::make('account_id')
                            ->label('Akun Kas/Bank')
                            ->options(fn () => Account::where('is_cash_bank', true)
                                ->orderBy('code')
                                ->pluck('name', 'id'))
                            ->required()
                            ->native(false),

                        Forms\Components\FileUpload::make('proof_file')
                            ->label('Bukti Transfer (opsional)')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                            ->disk('public')
                            ->directory('payment-proofs')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->nullable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->placeholder('Transfer BCA, nama pengirim, dsb.'),
                    ])
                    ->action(function (Invoice $record, array $data, OrderWorkflowService $service): void {
                        $payNumber = 'PAY-' . now()->format('Ym') . '-'
                            . str_pad((string) (Payment::count() + 1), 4, '0', STR_PAD_LEFT);

                        /** @var Payment $payment */
                        $payment = Payment::create([
                            'payment_number' => $payNumber,
                            'invoice_id'     => $record->id,
                            'customer_id'    => $record->customer_id,
                            'account_id'     => (int) $data['account_id'],
                            'amount'         => (float) $data['amount'],
                            'payment_date'   => $data['payment_date'],
                            'proof_file'     => $data['proof_file'] ?? null,
                            'status'         => PaymentStatus::Pending,
                        ]);

                        // Admin yang catat → langsung verifikasi
                        $service->verifyPayment(
                            payment:         $payment,
                            isApproved:      true,
                            verifiedBy:      (int) auth()->id(),
                            accountId:       (int) $data['account_id'],
                            rejectionReason: null,
                        );

                        Notification::make()
                            ->title('✅ Pembayaran Dicatat & Diverifikasi!')
                            ->body("Invoice {$record->invoice_number} sekarang Lunas.")
                            ->success()->send();
                    }),

                // ── DETAIL ────────────────────────────────────────────────
                Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('mark_overdue')
                        ->label('Tandai Jatuh Tempo')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $count = 0;
                            foreach ($records as $r) {
                                if ($r->status === InvoiceStatus::Unpaid && $r->due_date->isPast()) {
                                    $r->update(['status' => InvoiceStatus::Overdue]);
                                    $count++;
                                }
                            }
                            Notification::make()
                                ->title("{$count} invoice ditandai jatuh tempo")
                                ->success()->send();
                        }),
                ]),
            ])
            ->poll('5s');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Invoice::where('status', InvoiceStatus::PaymentUploaded->value)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Invoice dengan bukti bayar menunggu verifikasi';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-invoices') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view'  => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
