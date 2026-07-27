<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Produk';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\Select::make('category_id')
                ->relationship('category', 'name')
                ->label('Kategori')
                ->required(),
            Forms\Components\TextInput::make('sku')
                ->label('SKU / Kode Produk')
                ->required()
                ->default(fn () => 'PRD-' . rand(100, 999))
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')
                ->label('Nama Produk')
                ->required(),
            Forms\Components\TextInput::make('unit')
                ->label('Satuan')
                ->default('pcs')
                ->required(),
            Forms\Components\TextInput::make('buy_price')
                ->label('Harga Beli (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('sell_price')
                ->label('Harga Jual (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('stock')
                ->label('Stok Saat Ini')
                ->numeric()
                ->default(0)
                ->required(),
            Forms\Components\TextInput::make('min_stock')
                ->label('Stok Minimum Alert')
                ->numeric()
                ->default(5)
                ->required(),
            Forms\Components\FileUpload::make('image')
                ->label('Foto Produk')
                ->image()
                ->disk('public')
                ->directory('products'),
            Forms\Components\Textarea::make('description')
                ->label('Deskripsi Produk')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('Foto'),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Produk')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori')->sortable(),
                Tables\Columns\TextColumn::make('buy_price')->label('Harga Beli')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('sell_price')->label('Harga Jual')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->color(fn (Product $record) => $record->stock <= $record->min_stock ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-products') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
