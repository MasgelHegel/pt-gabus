<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan System';

    protected static ?int $navigationSort = 20;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $modelLabel = 'Log Aktivitas';

    protected static ?string $pluralModelLabel = 'Audit Log';

    // ─────────────────────────────────────────────────────────────────────────
    // Access — only superadmin
    // ─────────────────────────────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // read-only resource
    }

    // ─────────────────────────────────────────────────────────────────────────
    // View schema (detail panel)
    // ─────────────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Event')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                ->schema([
                    \Filament\Forms\Components\TextInput::make('event')
                        ->label('Event')
                        ->disabled(),

                    \Filament\Forms\Components\TextInput::make('auditable_type')
                        ->label('Model')
                        ->disabled()
                        ->formatStateUsing(fn (?string $state): string => $state
                            ? class_basename($state)
                            : '—'),

                    \Filament\Forms\Components\TextInput::make('auditable_id')
                        ->label('Record ID')
                        ->disabled(),

                    \Filament\Forms\Components\TextInput::make('user.name')
                        ->label('Dilakukan oleh')
                        ->disabled(),

                    \Filament\Forms\Components\TextInput::make('ip_address')
                        ->label('IP Address')
                        ->disabled(),

                    \Filament\Forms\Components\TextInput::make('created_at')
                        ->label('Waktu')
                        ->disabled()
                        ->formatStateUsing(fn ($state): string => $state
                            ? \Carbon\Carbon::parse($state)->format('d M Y, H:i:s')
                            : '—'),
                ]),

            Section::make('Perubahan Data')
                ->icon('heroicon-o-arrows-right-left')
                ->columns(2)
                ->schema([
                    \Filament\Forms\Components\Textarea::make('old_values')
                        ->label('Nilai Lama')
                        ->disabled()
                        ->rows(8)
                        ->formatStateUsing(fn ($state): string => $state
                            ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : '—'),

                    \Filament\Forms\Components\Textarea::make('new_values')
                        ->label('Nilai Baru')
                        ->disabled()
                        ->rows(8)
                        ->formatStateUsing(fn ($state): string => $state
                            ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                            : '—'),
                ]),

            Section::make('Informasi Teknis')
                ->icon('heroicon-o-code-bracket')
                ->collapsed()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('url')
                        ->label('URL')
                        ->disabled(),
                    \Filament\Forms\Components\Textarea::make('user_agent')
                        ->label('User Agent')
                        ->disabled()
                        ->rows(2),
                    \Filament\Forms\Components\TextInput::make('tags')
                        ->label('Tags')
                        ->disabled()
                        ->formatStateUsing(fn ($state): string => $state
                            ? implode(', ', (array) $state)
                            : '—'),
                ]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Table
    // ─────────────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->description(fn (AuditLog $record): string => $record->ip_address ?? ''),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable()
                    ->placeholder('System')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'info',
                        'login'   => 'primary',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),

                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? class_basename($state)
                        : '—')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('auditable_id')
                    ->label('ID')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Event')
                    ->options([
                        'created'  => 'Created',
                        'updated'  => 'Updated',
                        'deleted'  => 'Deleted',
                        'restored' => 'Restored',
                        'login'    => 'Login',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pengguna')
                    ->options(User::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->native(false),

                Tables\Filters\SelectFilter::make('auditable_type')
                    ->label('Model')
                    ->options(
                        AuditLog::distinct()->pluck('auditable_type', 'auditable_type')
                            ->filter()
                            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                    )
                    ->native(false),

                Tables\Filters\Filter::make('created_at')
                    ->label('Rentang Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('30s')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('Belum ada log aktivitas')
            ->emptyStateDescription('Log aktivitas akan muncul di sini setiap kali ada perubahan data di sistem.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pages
    // ─────────────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view'  => Pages\ViewAuditLog::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = AuditLog::whereDate('created_at', today())->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Log aktivitas hari ini';
    }
}
