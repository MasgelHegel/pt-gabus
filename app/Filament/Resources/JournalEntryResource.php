<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\JournalEntry;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Keuangan & Kas';
    protected static ?string $navigationLabel  = 'Jurnal Otomatis';
    protected static ?int    $navigationSort   = 4;
    protected static ?string $modelLabel       = 'Jurnal';
    protected static ?string $pluralModelLabel = 'Jurnal';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Jurnal')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('entry_number')
                        ->label('No. Jurnal')->disabled(),
                    Forms\Components\DatePicker::make('entry_date')
                        ->label('Tanggal')->disabled(),
                    Forms\Components\TextInput::make('reference')
                        ->label('Referensi')->disabled(),
                    Forms\Components\TextInput::make('createdBy.name')
                        ->label('Dibuat Oleh')->disabled(),
                    Forms\Components\Textarea::make('description')
                        ->label('Keterangan')
                        ->columnSpanFull()
                        ->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_number')
                    ->label('No. Jurnal')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium)
                    ->copyable(),

                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal')
                    ->date('d M Y')->sortable(),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(60)
                    ->tooltip(fn (JournalEntry $r): string => $r->description),

                Tables\Columns\TextColumn::make('lines_count')
                    ->label('Baris')
                    ->counts('lines')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->placeholder('Sistem')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn ($q) => $q->whereDate('entry_date', today())),

                Tables\Filters\Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn ($q) => $q
                        ->whereMonth('entry_date', now()->month)
                        ->whereYear('entry_date', now()->year)
                    ),
            ])
            ->actions([
                Actions\ViewAction::make()->label('Detail'),
            ])
            ->bulkActions([])
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJournalEntries::route('/'),
            'view'  => Pages\ViewJournalEntry::route('/{record}'),
        ];
    }
}
