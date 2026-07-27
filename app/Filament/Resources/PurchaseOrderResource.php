<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\PurchaseOrderStatus;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
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

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Procurement & Logistik';
    protected static ?string $navigationLabel  = 'Purchase Order (PO)';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'Purchase Order';
    protected static ?string $pluralModelLabel = 'Purchase Order';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi PO')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('po_number')->label('No. PO')->disabled(),
                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier', 'name')->label('Supplier')->disabled(),
                    Forms\Components\Select::make('status')
                        ->options(PurchaseOrderStatus::options())
                        ->label('Status')->required()->native(false),
                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total Amount')->prefix('Rp')->disabled(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')->columnSpanFull(),
                ]),

            Schemas\Components\Section::make('Daftar Produk (Purchase Order)')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->relationship('product', 'name')
                                ->label('Produk')
                                ->disabled(),
                            Forms\Components\TextInput::make('quantity')
                                ->label('Jumlah')
                                ->numeric()
                                ->disabled(),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Harga Satuan')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled(),
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled(),
                        ])
                        ->columns(4)
                        ->disabled()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('po_number')
                    ->label('No. PO')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('salesOrder.so_number')
                    ->label('Ref. SO')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PurchaseOrderStatus $state): string => $state->color())
                    ->formatStateUsing(fn (PurchaseOrderStatus $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl PO')
                    ->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(PurchaseOrderStatus::options())
                    ->native(false),
            ])
            ->actions([
                // ── TERIMA BARANG ─────────────────────────────────────────
                Actions\Action::make('receive_goods')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (PurchaseOrder $r): bool =>
                        $r->status === PurchaseOrderStatus::Ordered
                    )
                    ->form([
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Gudang Penerima')
                            ->options(fn () => Warehouse::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Penerimaan')->rows(2),
                    ])
                    ->action(function (PurchaseOrder $record, array $data, OrderWorkflowService $service): void {
                        $receipt = $service->processGoodsReceipt(
                            $record,
                            (int) $data['warehouse_id'],
                            (int) auth()->id(),
                            $data['notes'] ?? null
                        );
                        Notification::make()
                            ->title("📦 Barang Masuk #{$receipt->receipt_number} diterima!")
                            ->body('QC otomatis dibuat (status: Menunggu QC).')
                            ->success()->send();
                    }),

                Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = PurchaseOrder::where('status', PurchaseOrderStatus::Ordered->value)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'PO menunggu penerimaan barang';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-purchase-orders') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrders::route('/'),
            'view'  => Pages\ViewPurchaseOrder::route('/{record}'),
        ];
    }
}
