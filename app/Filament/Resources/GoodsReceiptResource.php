<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\QCStatus;
use App\Filament\Resources\GoodsReceiptResource\Pages;
use App\Models\GoodsReceipt;
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

class GoodsReceiptResource extends Resource
{
    protected static ?string $model = GoodsReceipt::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Procurement & Logistik';
    protected static ?string $navigationLabel  = 'Barang Masuk & QC';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Goods Receipt';
    protected static ?string $pluralModelLabel = 'Goods Receipt';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Penerimaan')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('receipt_number')->label('No. GR')->disabled(),
                    Forms\Components\Select::make('warehouse_id')
                        ->relationship('warehouse', 'name')->label('Gudang')->disabled(),
                    Forms\Components\DatePicker::make('received_date')->label('Tgl Terima')->disabled(),
                    Forms\Components\Textarea::make('notes')->label('Catatan')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('No. Goods Receipt')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('No. PO')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Gudang')->sortable(),

                Tables\Columns\TextColumn::make('received_date')
                    ->label('Tgl Terima')
                    ->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('qcCheck.status')
                    ->label('Status QC')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state instanceof QCStatus => $state->color(),
                        $state === 'passed'        => 'success',
                        $state === 'failed'        => 'danger',
                        default                    => 'warning',
                    })
                    ->formatStateUsing(fn ($state): string =>
                        $state instanceof QCStatus
                            ? $state->label()
                            : match ($state) {
                                'passed'  => 'Lolos QC',
                                'failed'  => 'Gagal QC',
                                'pending' => 'Menunggu QC',
                                default   => ucfirst((string) $state),
                            }
                    ),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('qc_status')
                    ->label('Status QC')
                    ->options([
                        'pending' => 'Menunggu QC',
                        'passed'  => 'Lolos QC',
                        'failed'  => 'Gagal QC',
                    ])
                    ->query(fn ($query, array $data) =>
                        $data['value']
                            ? $query->whereHas('qcCheck', fn ($q) => $q->where('status', $data['value']))
                            : $query
                    )
                    ->native(false),
            ])
            ->actions([
                // ── VERIFIKASI QC ─────────────────────────────────────────
                Actions\Action::make('verify_qc')
                    ->label('Verifikasi QC')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn (GoodsReceipt $r): bool =>
                        ! $r->qcCheck || $r->qcCheck->status === QCStatus::Pending
                    )
                    ->modalHeading(fn (GoodsReceipt $r): string =>
                        "Verifikasi QC — {$r->receipt_number}"
                    )
                    ->form([
                        Forms\Components\Radio::make('qc_result')
                            ->label('Hasil Inspeksi QC')
                            ->options([
                                'passed' => '✅ Lolos QC — Stok akan otomatis ditambahkan ke gudang',
                                'failed' => '❌ Gagal QC — Stok tidak ditambahkan, barang dikembalikan',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Inspeksi')
                            ->rows(3)
                            ->placeholder('Kondisi barang, catatan kerusakan, dsb.'),
                    ])
                    ->action(function (GoodsReceipt $record, array $data, OrderWorkflowService $service): void {
                        $isPassed = $data['qc_result'] === 'passed';

                        $service->verifyQCCheck(
                            $record,
                            $isPassed,
                            (int) auth()->id(),
                            $data['notes'] ?? null
                        );

                        if ($isPassed) {
                            Notification::make()
                                ->title('✅ QC Lolos! Stok otomatis diperbarui di gudang.')
                                ->success()->send();
                        } else {
                            Notification::make()
                                ->title('❌ QC Gagal. Stok tidak ditambahkan.')
                                ->warning()->send();
                        }
                    }),

                Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = GoodsReceipt::whereHas(
            'qcCheck',
            fn ($q) => $q->where('status', QCStatus::Pending->value)
        )->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Menunggu verifikasi QC';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-purchase-orders') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGoodsReceipts::route('/'),
            'view'  => Pages\ViewGoodsReceipt::route('/{record}'),
        ];
    }
}
