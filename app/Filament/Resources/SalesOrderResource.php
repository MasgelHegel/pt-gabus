<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\SalesOrderStatus;
use App\Filament\Resources\SalesOrderResource\Pages;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Models\Supplier;
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
use Illuminate\Support\Facades\DB;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Operasional Sales';
    protected static ?string $navigationLabel  = 'Sales Order (SO)';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Sales Order';
    protected static ?string $pluralModelLabel = 'Sales Order';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi SO')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('so_number')->label('No. SO')->disabled(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')->label('Customer')->disabled(),
                    Forms\Components\Select::make('sales_id')
                        ->relationship('sales', 'name')
                        ->label('Sales')
                        ->placeholder('Pilih Sales')
                        ->options(function () {
                            $salesRoleId = DB::table('roles')->where('name', 'sales')->value('id');
                            if (! $salesRoleId) return [];
                            return DB::table('model_has_roles')
                                ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                                ->where('model_has_roles.role_id', $salesRoleId)
                                ->whereNull('users.deleted_at')
                                ->pluck('users.name', 'users.id')
                                ->toArray();
                        })
                        ->nullable()
                        ->searchable()
                        ->preload()
                        ->native(false),
                    Forms\Components\Select::make('status')
                        ->options(SalesOrderStatus::options())
                        ->label('Status')->required()->native(false),
                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total')->prefix('Rp')->disabled(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')->columnSpanFull(),
                ]),

            Schemas\Components\Section::make('Daftar Produk (Sales Order)')
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
                Tables\Columns\TextColumn::make('so_number')
                    ->label('No. SO')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('items_list')
                    ->label('Detail Barang')
                    ->state(fn (SalesOrder $record): string => 
                        $record->items->map(fn ($item) => "{$item->product?->name} ({$item->quantity} {$item->product?->unit})")->join(', ')
                    )
                    ->wrap()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('sales.name')
                    ->label('Sales')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (SalesOrderStatus $state): string => $state->color())
                    ->formatStateUsing(fn (SalesOrderStatus $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl SO')
                    ->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(SalesOrderStatus::options())
                    ->native(false),
            ])
            ->actions([
                // ── BUAT PURCHASE ORDER ───────────────────────────────────
                Actions\Action::make('create_po')
                    ->label('Buat PO')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('warning')
                    ->visible(fn (SalesOrder $r): bool =>
                        in_array($r->status, [
                            SalesOrderStatus::Processing,
                            SalesOrderStatus::ReadyToShip,
                        ]) && ! $r->purchaseOrders()->exists()
                    )
                    ->form([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn () => Supplier::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan PO')->rows(2),
                    ])
                    ->action(function (SalesOrder $record, array $data, OrderWorkflowService $service): void {
                        $po = $service->createPurchaseOrder(
                            $record,
                            (int) $data['supplier_id'],
                            $data['notes'] ?? null
                        );
                        Notification::make()
                            ->title("✅ PO #{$po->po_number} berhasil dibuat ke supplier!")
                            ->success()->send();
                    }),

                // ── KIRIM BARANG ──────────────────────────────────────────
                Actions\Action::make('ship')
                    ->label('Kirim Barang')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(fn (SalesOrder $r): bool =>
                        in_array($r->status, [
                            SalesOrderStatus::Processing,
                            SalesOrderStatus::ReadyToShip,
                        ])
                    )
                    ->form([
                        Forms\Components\TextInput::make('courier')
                            ->label('Ekspedisi / Kurir')
                            ->required()
                            ->placeholder('JNE, TIKI, SiCepat, dll.'),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Nomor Resi')
                            ->required()
                            ->default('RESI-' . strtoupper(substr(uniqid(), -8))),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Pengiriman')->rows(2),
                    ])
                    ->action(function (SalesOrder $record, array $data, OrderWorkflowService $service): void {
                        $shipment = $service->shipSalesOrder(
                            $record,
                            $data['courier'],
                            $data['tracking_number'],
                            $data['notes'] ?? null
                        );
                        Notification::make()
                            ->title("🚚 Pengiriman #{$shipment->shipment_number} diproses!")
                            ->success()->send();
                    }),

                // ── SIAP KIRIM ────────────────────────────────────────────
                Actions\Action::make('ready_to_ship')
                    ->label('Tandai Siap Kirim')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (SalesOrder $r): bool =>
                        $r->status === SalesOrderStatus::Processing
                    )
                    ->requiresConfirmation()
                    ->action(function (SalesOrder $record): void {
                        $record->update(['status' => SalesOrderStatus::ReadyToShip]);
                        Notification::make()
                            ->title("SO #{$record->so_number} siap dikirim")
                            ->success()->send();
                    }),

                Actions\ViewAction::make()->label('Detail'),

                // ── HAPUS SO ──────────────────────────────────────────────
                Actions\Action::make('delete_so')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn () => auth()->user()?->isSuperAdmin() ?? false)
                    ->requiresConfirmation()
                    ->modalHeading(fn (SalesOrder $r): string => "Hapus SO {$r->so_number}?")
                    ->modalDescription('SO beserta invoice, payment, jurnal, shipment, dan PO terkait akan dihapus permanen. Order asal akan dikembalikan ke status Submitted.')
                    ->modalSubmitActionLabel('Ya, Hapus Permanen')
                    ->action(function (SalesOrder $record): void {
                        DB::transaction(function () use ($record): void {
                            // 1. Hapus invoice & turunannya
                            if ($record->invoice) {
                                $invoice = $record->invoice;

                                // Kembalikan piutang jika invoice belum lunas
                                if ($invoice->status !== InvoiceStatus::Paid && $invoice->status !== InvoiceStatus::Cancelled) {
                                    Customer::where('id', $invoice->customer_id)
                                        ->decrement('piutang_balance', (float) $invoice->total_amount);
                                }

                                // Hapus jurnal invoice
                                JournalEntry::where('reference', $invoice->invoice_number)->each(function ($j): void {
                                    $j->lines()->delete();
                                    $j->forceDelete();
                                });

                                // Hapus payment & jurnal payment
                                $invoice->payments()->each(function ($payment): void {
                                    JournalEntry::where('reference', $payment->payment_number)->each(function ($j): void {
                                        $j->lines()->delete();
                                        $j->forceDelete();
                                    });
                                    $payment->forceDelete();
                                });

                                $invoice->items()->delete();
                                $invoice->forceDelete();
                            }

                            // 2. Hapus shipment
                            $record->shipment?->forceDelete();

                            // 3. Hapus PO & PO items
                            $record->purchaseOrders()->each(function ($po): void {
                                $po->items()->delete();
                                $po->forceDelete();
                            });

                            // 4. Hapus SO items
                            $record->items()->delete();

                            // 5. Kembalikan Order ke status Submitted
                            if ($record->order) {
                                $record->order->update(['status' => OrderStatus::Submitted]);
                            }

                            // 6. Hapus SO
                            $record->forceDelete();
                        });

                        Notification::make()
                            ->title('Sales Order dihapus permanen')
                            ->body('Semua data terkait (invoice, payment, jurnal) telah dihapus. Order dikembalikan ke status Submitted.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('bulk_delete_so')
                        ->label('Hapus Semua Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->visible(fn () => auth()->user()?->isSuperAdmin() ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Hapus SO Terpilih?')
                        ->modalDescription('Semua SO yang dipilih beserta invoice, payment, jurnal, dan shipment akan dihapus permanen.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->action(function ($records): void {
                            $count = 0;
                            DB::transaction(function () use ($records, &$count): void {
                                foreach ($records as $record) {
                                    if ($record->invoice) {
                                        $invoice = $record->invoice;

                                        if ($invoice->status !== \App\Enums\InvoiceStatus::Paid && $invoice->status !== \App\Enums\InvoiceStatus::Cancelled) {
                                            Customer::where('id', $invoice->customer_id)
                                                ->decrement('piutang_balance', (float) $invoice->total_amount);
                                        }

                                        JournalEntry::where('reference', $invoice->invoice_number)->each(function ($j): void {
                                            $j->lines()->delete();
                                            $j->forceDelete();
                                        });

                                        $invoice->payments()->each(function ($payment): void {
                                            JournalEntry::where('reference', $payment->payment_number)->each(function ($j): void {
                                                $j->lines()->delete();
                                                $j->forceDelete();
                                            });
                                            $payment->forceDelete();
                                        });

                                        $invoice->items()->delete();
                                        $invoice->forceDelete();
                                    }

                                    $record->shipment?->forceDelete();

                                    $record->purchaseOrders()->each(function ($po): void {
                                        $po->items()->delete();
                                        $po->forceDelete();
                                    });

                                    $record->items()->delete();

                                    if ($record->order) {
                                        $record->order->update(['status' => OrderStatus::Submitted]);
                                    }

                                    $record->forceDelete();
                                    $count++;
                                }
                            });

                            Notification::make()
                                ->title("{$count} Sales Order dihapus permanen")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->poll('5s');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->with(['items.product']);
        $user  = auth()->user();

        if (! $user) {
            return $query;
        }

        // SuperAdmin dan Admin lihat semua SO
        if ($user->isSuperAdmin() || $user->hasRole(\App\Enums\UserRole::Admin->value)) {
            return $query;
        }

        // Sales hanya lihat SO miliknya
        if ($user->hasRole(\App\Enums\UserRole::Sales->value)) {
            return $query->where('sales_id', $user->id);
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        $query = SalesOrder::whereIn('status', [
            SalesOrderStatus::Processing->value,
            SalesOrderStatus::ReadyToShip->value,
        ]);
        $user = auth()->user();

        if ($user && $user->hasRole(\App\Enums\UserRole::Sales->value) && !$user->isSuperAdmin() && !$user->hasRole(\App\Enums\UserRole::Admin->value)) {
            $query->where('sales_id', $user->id);
        }

        $count = $query->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'SO sedang diproses';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-sales-orders') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'view'  => Pages\ViewSalesOrder::route('/{record}'),
        ];
    }
}
