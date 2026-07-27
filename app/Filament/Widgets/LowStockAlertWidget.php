<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlertWidget extends BaseWidget
{
    protected static ?string $heading    = 'Stok Hampir Habis';
    protected static ?int    $sort       = 4;
    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = '20s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->whereColumn('stock', '<=', 'min_stock')
                    ->orderBy('stock')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Produk')
                    ->searchable()
                    ->weight(\Filament\Support\Enums\FontWeight::Medium),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color(fn (Product $r): string => $r->stock === 0 ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Min')
                    ->color('gray'),
            ])
            ->actions([
                Actions\Action::make('po')
                    ->label('Buat PO')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('warning')
                    ->url(fn (Product $r): string => \App\Filament\Resources\PurchaseOrderResource::getUrl('create') . '?product_id=' . $r->id),
            ])
            ->paginated(false);
    }
}
