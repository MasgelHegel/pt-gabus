<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Gudang';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('code')
                ->label('Kode Gudang')
                ->required()
                ->default(fn () => 'WH-' . rand(10, 99))
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')
                ->label('Nama Gudang')
                ->required(),
            Forms\Components\Textarea::make('address')
                ->label('Alamat Gudang')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode Gudang')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama Gudang')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('address')->label('Alamat'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-warehouses') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit'   => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}
