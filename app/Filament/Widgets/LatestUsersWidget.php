<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestUsersWidget extends BaseWidget
{
    protected static ?string $heading = 'Pengguna Terbaru';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->with(['roles', 'company'])
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->defaultImageUrl(fn (User $record) => $record->avatar_url)
                    ->circular()
                    ->size(36),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->weight(\Filament\Support\Enums\FontWeight::Medium)
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->separator(','),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (UserStatus $state): string => $state->color())
                    ->formatStateUsing(fn (UserStatus $state): string => $state->label()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Actions\Action::make('edit')
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil'),
            ])
            ->paginated(false);
    }
}
