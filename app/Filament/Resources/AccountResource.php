<?php

namespace App\Filament\Resources;

use App\Enums\AccountType;
use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan & Kas';

    protected static ?string $navigationLabel = 'Kas & Akun Keuangan (COA)';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('code')
                ->label('Kode Akun')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')
                ->label('Nama Akun')
                ->required(),
            Forms\Components\Select::make('type')
                ->label('Tipe Akun')
                ->options(AccountType::class)
                ->required(),
            Forms\Components\TextInput::make('balance')
                ->label('Saldo Saat Ini (Rp)')
                ->numeric()
                ->prefix('Rp')
                ->default(0),
            Forms\Components\Toggle::make('is_cash_bank')
                ->label('Akun Kas / Bank (Menerima Pembayaran)')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode Akun')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama Akun')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Akun')
                    ->formatStateUsing(fn ($state) => $state instanceof AccountType ? $state->label() : ucfirst((string)$state))
                    ->badge(),
                Tables\Columns\TextColumn::make('balance')->label('Saldo')->money('IDR')->badge()->color('success')->sortable(),
                Tables\Columns\IconColumn::make('is_cash_bank')->label('Kas/Bank')->boolean(),
            ])
            ->actions([
                Actions\EditAction::make(),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-accounts') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit'   => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
