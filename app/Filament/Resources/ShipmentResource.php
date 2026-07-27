<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Models\Shipment;
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

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Procurement & Logistik';
    protected static ?string $navigationLabel  = 'Pengiriman Barang';
    protected static ?int    $navigationSort   = 3;
    protected static ?string $modelLabel       = 'Pengiriman';
    protected static ?string $pluralModelLabel = 'Pengiriman';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Pengiriman')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('shipment_number')->label('No. Pengiriman')->disabled(),
                    Forms\Components\TextInput::make('courier')->label('Ekspedisi')->disabled(),
                    Forms\Components\TextInput::make('tracking_number')->label('Resi')->disabled(),
                    Forms\Components\DateTimePicker::make('shipped_at')->label('Waktu Kirim')->disabled(),
                    Forms\Components\DateTimePicker::make('delivered_at')->label('Waktu Diterima')->disabled(),
                    Forms\Components\DateTimePicker::make('customer_confirmed_at')->label('Dikonfirmasi Customer')->disabled(),
                    Forms\Components\Textarea::make('notes')->label('Catatan')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shipment_number')
                    ->label('No. Pengiriman')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('salesOrder.so_number')
                    ->label('No. SO')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('salesOrder.customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courier')
                    ->label('Kurir'),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Resi')
                    ->copyable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered'          => 'success',
                        'customer_confirmed' => 'warning',
                        'shipped'            => 'primary',
                        'processing'         => 'warning',
                        default              => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'delivered'          => 'Diterima Customer',
                        'customer_confirmed' => 'Dikonfirmasi Customer',
                        'shipped'            => 'Dikirim',
                        'processing'         => 'Diproses',
                        default              => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Waktu Kirim')
                    ->dateTime('d M Y H:i')->sortable(),

                Tables\Columns\TextColumn::make('customer_confirmed_at')
                    ->label('Konfirmasi Customer')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum dikonfirmasi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Waktu Diterima')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum diterima')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'processing'         => 'Diproses',
                        'shipped'            => 'Dikirim',
                        'customer_confirmed' => 'Dikonfirmasi Customer',
                        'delivered'          => 'Diterima Customer',
                    ])
                    ->native(false),
            ])
            ->actions([
                // ── VERIFIKASI KONFIRMASI CUSTOMER ─────────────────────────
                Actions\Action::make('verify_customer')
                    ->label('Verifikasi Konfirmasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Shipment $r): bool => $r->status === 'customer_confirmed')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Konfirmasi Customer')
                    ->modalDescription(fn (Shipment $r): string =>
                        "Customer mengkonfirmasi pengiriman {$r->shipment_number} sudah diterima. Verifikasi sebagai diterima?"
                    )
                    ->action(function (Shipment $record, OrderWorkflowService $service): void {
                        $service->markShipmentDelivered($record);
                        Notification::make()
                            ->title("✅ Pengiriman {$record->shipment_number} diverifikasi diterima!")
                            ->success()->send();
                    }),

                // ── TOLAK KONFIRMASI CUSTOMER ─────────────────────────────
                Actions\Action::make('reject_customer')
                    ->label('Tolak Konfirmasi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Shipment $r): bool => $r->status === 'customer_confirmed')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Konfirmasi Customer')
                    ->modalDescription(fn (Shipment $r): string =>
                        "Tolak konfirmasi pengiriman {$r->shipment_number}? Status akan dikembalikan ke Dikirim."
                    )
                    ->action(function (Shipment $record, OrderWorkflowService $service): void {
                        $service->rejectCustomerConfirmation($record);
                        Notification::make()
                            ->title("Konfirmasi pengiriman {$record->shipment_number} ditolak.")
                            ->warning()->send();
                    }),

                // ── KONFIRMASI DITERIMA (langsung, tanpa customer) ────────
                Actions\Action::make('mark_delivered')
                    ->label('Konfirmasi Diterima')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Shipment $r): bool => $r->status === 'shipped')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Barang Diterima')
                    ->modalDescription(fn (Shipment $r): string =>
                        "Konfirmasi pengiriman {$r->shipment_number} sudah diterima customer?"
                    )
                    ->action(function (Shipment $record, OrderWorkflowService $service): void {
                        $service->markShipmentDelivered($record);
                        Notification::make()
                            ->title("✅ Pengiriman {$record->shipment_number} dikonfirmasi diterima!")
                            ->success()->send();
                    }),

                Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([])
            ->poll('5s');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Shipment::whereIn('status', ['shipped', 'customer_confirmed'])->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Dalam pengiriman / menunggu verifikasi';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-shipments') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShipments::route('/'),
            'view'  => Pages\ViewShipment::route('/{record}'),
        ];
    }
}
