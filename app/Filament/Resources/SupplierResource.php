<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Supplier';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('code')
                ->label('Kode Supplier')
                ->required()
                ->default(fn () => 'SUP-' . rand(100, 999))
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')
                ->label('Nama Supplier')
                ->required(),
            Forms\Components\TextInput::make('company_name')
                ->label('Nama Perusahaan'),
            Forms\Components\TextInput::make('email')
                ->email(),
            Forms\Components\TextInput::make('phone')
                ->tel(),
            Forms\Components\Textarea::make('address')
                ->label('Alamat')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama Supplier')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('company_name')->label('Perusahaan')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Telepon'),
                Tables\Columns\TextColumn::make('email')->label('Email'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-suppliers') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
