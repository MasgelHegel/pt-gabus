<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
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

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Operasional Sales';
    protected static ?string $navigationLabel = 'Order (Customer)';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'Order';
    protected static ?string $pluralModelLabel = 'Order';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Order')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->label('Nomor Order')->disabled(),

                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->label('Customer')->required(),

                    Forms\Components\Select::make('sales_id')
                        ->relationship('sales', 'name')
                        ->label('Sales Handler')->nullable(),

                    Forms\Components\Select::make('status')
                        ->options(OrderStatus::options())
                        ->label('Status')->required()->native(false),

                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total Nominal')->numeric()->prefix('Rp')->disabled(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan Order')->columnSpanFull(),
                ]),

            Schemas\Components\Section::make('Daftar Produk yang Dipesan')
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
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Order')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Nama Dapur / Customer')
                    ->searchable()->sortable()
                    ->description(fn (Order $record): string =>
                        $record->notes
                            ? collect(explode("\n", $record->notes))
                                ->filter(fn($l) => str_contains($l, 'No HP:'))
                                ->map(fn($l) => trim(str_replace(['📞', 'No HP:', ' '], ['','',''], $l)))
                                ->first() ?? ''
                            : ''
                    ),

                Tables\Columns\TextColumn::make('items_list')
                    ->label('Detail Barang')
                    ->state(fn (Order $record): string => 
                        $record->items->map(fn ($item) => "{$item->product?->name} ({$item->quantity} {$item->product?->unit})")->join(', ')
                    )
                    ->wrap()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('sales.name')
                    ->label('Sales')
                    ->placeholder('Belum diassign')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')->sortable()
                    ->weight(FontWeight::SemiBold),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => $state->color())
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Order')
                    ->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(OrderStatus::options())
                    ->native(false),

                Tables\Filters\Filter::make('pending')
                    ->label('Butuh Tindakan')
                    ->query(fn ($q) => $q->whereIn('status', [
                        OrderStatus::Submitted->value,
                        OrderStatus::SalesReviewed->value,
                    ])),
            ])
            ->actions([
                // ── SALES REVIEW ──────────────────────────────────────────
                Actions\Action::make('sales_review')
                    ->label('Sales Review')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Order $r): bool =>
                        in_array($r->status, [OrderStatus::Submitted, OrderStatus::Draft])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Sales Review')
                    ->modalDescription(fn (Order $r): string =>
                        "Order {$r->order_number} akan ditandai sudah ditinjau Sales."
                    )
                    ->action(function (Order $record, OrderWorkflowService $service): void {
                        $service->salesReviewOrder($record, (int) auth()->id());

                        Notification::make()
                            ->title("Order {$record->order_number} sudah ditinjau Sales")
                            ->success()->send();
                    }),

                // ── ADMIN APPROVE → AUTO SO + INVOICE ─────────────────────
                Actions\Action::make('admin_approve')
                    ->label('Approve & Buat SO')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->visible(fn (Order $r): bool =>
                        in_array($r->status, [OrderStatus::Submitted, OrderStatus::SalesReviewed])
                    )
                    ->modalHeading('Approve Order & Buat Sales Order')
                    ->modalDescription(fn (Order $r): string =>
                        "Approve order {$r->order_number}? Sales Order, Invoice, dan Piutang akan dibuat otomatis."
                    )
                    ->form([
                        Forms\Components\Select::make('sales_id')
                            ->label('Sales Handler')
                            ->options(function () {
                                $salesRoleId = \Illuminate\Support\Facades\DB::table('roles')
                                    ->where('name', 'sales')->value('id');
                                if (! $salesRoleId) return [];
                                return \Illuminate\Support\Facades\DB::table('model_has_roles')
                                    ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                                    ->where('model_has_roles.role_id', $salesRoleId)
                                    ->whereNull('users.deleted_at')
                                    ->pluck('users.name', 'users.id')
                                    ->toArray();
                            })
                            ->default(fn (Order $record) => $record->sales_id)
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->helperText('Pilih sales handler untuk Sales Order ini.'),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Jatuh Tempo Invoice')
                            ->required()
                            ->native(false)
                            ->default(fn (Order $record) => now()->addDays($record->customer?->due_period_days ?? 30)->format('Y-m-d'))
                            ->minDate(now()->toDateString())
                            ->displayFormat('d/m/Y')
                            ->helperText('Tentukan kapan invoice harus dibayar oleh customer.'),

                        Forms\Components\Radio::make('due_preset')
                            ->label('Atau pilih cepat')
                            ->options([
                                '7'  => '7 hari',
                                '14' => '14 hari',
                                '30' => '30 hari',
                                '45' => '45 hari',
                                '60' => '60 hari',
                            ])
                            ->default(fn (Order $record) => (string) ($record->customer?->due_period_days ?? 30))
                            ->inline()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('due_date', now()->addDays((int) $state)->format('Y-m-d'));
                                }
                            }),

                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('Setelah approve: Sales Order + Invoice + Piutang customer dibuat otomatis.'),
                    ])
                    ->action(function (Order $record, array $data, OrderWorkflowService $service): void {
                        $dueDate = \Carbon\Carbon::parse($data['due_date']);
                        $dueDays = (int) now()->diffInDays($dueDate, false);
                        $dueDays = max(1, $dueDays);

                        if (!empty($data['sales_id'])) {
                            $record->update(['sales_id' => $data['sales_id']]);
                        }

                        $so = $service->adminApproveOrder($record, (int) auth()->id(), 0.0, $dueDays);

                        Notification::make()
                            ->title("Order Diapprove! SO #{$so->so_number} Dibuat")
                            ->body("Invoice jatuh tempo: {$dueDate->format('d M Y')} ({$dueDays} hari).")
                            ->success()->send();
                    }),

                // ── REJECT ────────────────────────────────────────────────
                Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $r): bool =>
                        in_array($r->status, [
                            OrderStatus::Submitted,
                            OrderStatus::Draft,
                            OrderStatus::SalesReviewed,
                        ])
                    )
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Alasan Penolakan')
                            ->required()->rows(3),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $oldStatus = $record->status;

                        $record->update([
                            'status' => OrderStatus::Rejected,
                            'notes'  => $data['notes'],
                        ]);

                        OrderStatusChanged::dispatch($record->fresh(), $oldStatus, $record->status);

                        Notification::make()
                            ->title("Order {$record->order_number} Ditolak")
                            ->warning()->send();
                    }),

                Actions\ViewAction::make()->label('Detail'),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('5s');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::whereIn('status', [
            OrderStatus::Submitted->value,
            OrderStatus::SalesReviewed->value,
        ])->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Order menunggu tindakan';
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['items.product']);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-orders') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
            'view'   => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
