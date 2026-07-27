<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Product;
use App\Exports\StockExport;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

class LaporanStok extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Stok Barang';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.laporan-table';

    public function getTitle(): string
    {
        return 'Laporan Stok & Aset Barang';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-reports') || auth()->user()?->isSuperAdmin();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return Excel::download(new StockExport, 'laporan-stok-barang-' . now()->format('Ymd') . '.xlsx');
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->latest('sku')
            )
            ->heading('Stok & Aset Barang')
            ->columns([
                TextColumn::make('sku')->label('SKU')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Produk')->searchable()->weight(FontWeight::Medium),
                TextColumn::make('category.name')->label('Kategori')->searchable(),
                TextColumn::make('stock')->label('Stok Saat Ini')->numeric()->sortable()
                    ->badge()
                    ->color(fn (Product $record) => $record->stock <= $record->min_stock ? 'danger' : 'success'),
                TextColumn::make('unit')->label('Satuan'),
                TextColumn::make('buy_price')->label('Harga Beli')->money('IDR')->sortable(),
                TextColumn::make('sell_price')->label('Harga Jual')->money('IDR')->sortable(),
                TextColumn::make('asset_value')
                    ->label('Nilai Aset')
                    ->money('IDR')
                    ->state(fn (Product $record): float => $record->stock * (float) $record->buy_price)
                    ->color('info')
                    ->weight(FontWeight::Bold),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->native(false),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated(true);
    }
}
