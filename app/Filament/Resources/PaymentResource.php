<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Account;
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

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Keuangan & Kas';
    protected static ?string $navigationLabel = 'Verifikasi Pembayaran';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Pembayaran';
    protected static ?string $pluralModelLabel = 'Pembayaran';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Detail Pembayaran')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('payment_number')->label('No. Pembayaran')->disabled(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')->label('Customer')->disabled(),
                    Forms\Components\Select::make('invoice_id')
                        ->relationship('invoice', 'invoice_number')->label('Invoice')->disabled(),
                    Forms\Components\TextInput::make('amount')->label('Nominal')->prefix('Rp')->disabled(),
                    Forms\Components\DatePicker::make('payment_date')->label('Tgl Bayar')->disabled(),
                    Forms\Components\Select::make('status')
                        ->options(PaymentStatus::options())->label('Status')->disabled(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([

            // ── Kiri: detail pembayaran ────────────────────────────────
            Schemas\Components\Section::make('Detail Pembayaran')
                ->columnSpan(1)
                ->schema([
                    Forms\Components\TextInput::make('payment_number')
                        ->label('No. Pembayaran')
                        ->disabled(),

                    Forms\Components\TextInput::make('invoice.invoice_number')
                        ->label('No. Invoice')
                        ->disabled(),

                    Forms\Components\TextInput::make('customer.name')
                        ->label('Customer')
                        ->disabled(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Nominal Dibayar')
                        ->prefix('Rp')
                        ->disabled(),

                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Tanggal Bayar')
                        ->disabled(),

                    Forms\Components\TextInput::make('status')
                        ->label('Status')
                        ->formatStateUsing(fn ($state) => $state instanceof PaymentStatus ? $state->label() : $state)
                        ->disabled(),

                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->disabled()
                        ->visible(fn ($record) => (bool) $record?->rejection_reason),
                ]),

            // ── Kanan: bukti transfer ──────────────────────────────────
            Schemas\Components\Section::make('Bukti Transfer')
                ->columnSpan(1)
                ->schema([
                    Forms\Components\ViewField::make('proof_file')
                        ->label('')
                        ->view('filament.forms.components.payment-proof'),
                ]),

        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('No. Pembayaran')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->label('No. Invoice')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tgl Bayar')
                    ->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PaymentStatus $state): string => $state->color())
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Upload')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(PaymentStatus::options())
                    ->native(false),
            ])
            ->actions([
                // ── LIHAT BUKTI — langsung buka file ───────────────────────
                Actions\Action::make('view_proof')
                    ->label('Lihat Bukti')
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->visible(fn (Payment $r): bool => (bool) $r->proof_file)
                    ->url(fn (Payment $r): string => $r->proof_url)
                    ->openUrlInNewTab(),

                // ── VERIFIKASI ────────────────────────────────────────────
                Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $r): bool => $r->status === PaymentStatus::Pending)
                    ->form([
                        Forms\Components\Select::make('account_id')
                            ->label('Akun Kas/Bank Penerima')
                            ->options(fn () => Account::where('is_cash_bank', true)
                                ->orderBy('code')->pluck('name', 'id'))
                            ->required()
                            ->native(false),
                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('Setelah verifikasi: Invoice → Lunas, Piutang berkurang, Jurnal otomatis dibuat.'),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Pembayaran')
                    ->action(function (Payment $record, array $data, OrderWorkflowService $service): void {
                        $service->verifyPayment(
                            payment:         $record,
                            isApproved:      true,
                            verifiedBy:      (int) auth()->id(),
                            accountId:       (int) $data['account_id'],
                            rejectionReason: null,
                        );

                        Notification::make()
                            ->title('Pembayaran Diverifikasi!')
                            ->body('Invoice lunas, piutang berkurang, jurnal dibuat otomatis.')
                            ->success()->send();
                    }),

                // ── TOLAK ─────────────────────────────────────────────────
                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $r): bool => $r->status === PaymentStatus::Pending)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()->rows(3)
                            ->placeholder('Contoh: Nominal tidak sesuai, bukti tidak jelas...'),
                    ])
                    ->action(function (Payment $record, array $data, OrderWorkflowService $service): void {
                        $service->verifyPayment(
                            payment:         $record,
                            isApproved:      false,
                            verifiedBy:      (int) auth()->id(),
                            accountId:       null,
                            rejectionReason: $data['rejection_reason'],
                        );

                        Notification::make()
                            ->title('Pembayaran Ditolak')
                            ->warning()->send();
                    }),
            ])
            ->bulkActions([])
            ->poll('5s');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Payment::where('status', PaymentStatus::Pending->value)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Bukti bayar menunggu verifikasi';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-payments') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view'  => Pages\ViewPayment::route('/{record}'),
        ];
    }
}
