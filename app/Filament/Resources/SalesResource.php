<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\SalesResource\Pages;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class SalesResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'SDM';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Sales';

    protected static ?string $pluralModelLabel = 'Sales';

    protected static ?string $navigationLabel = 'Sales';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Akun')
                ->description('Data dasar akun sales')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('No. Telepon')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(UserStatus::options())
                        ->default(UserStatus::Active->value)
                        ->required()
                        ->native(false),
                ]),

            Schemas\Components\Section::make('Password')
                ->description('Kosongkan jika tidak ingin mengubah password')
                ->icon('heroicon-o-lock-closed')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->minLength(8)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->revealable()
                        ->same('password')
                        ->dehydrated(false)
                        ->required(fn (string $operation): bool => $operation === 'create'),
                ]),

            Schemas\Components\Section::make('Peran & Akses')
                ->icon('heroicon-o-shield-check')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('company_name')
                        ->label('Perusahaan')
                        ->placeholder('Ketik nama perusahaan')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?User $record) {
                            if ($record && $record->company) {
                                $component->state($record->company->name);
                            }
                        })
                        ->saveRelationshipsUsing(function (User $record, $state) {
                            $name = trim($state ?? '');
                            if (empty($name)) {
                                $record->company_id = null;
                            } else {
                                $company = \App\Models\Company::firstOrCreate(['name' => $name]);
                                $record->company_id = $company->id;
                            }
                            $record->save();
                        }),

                    Forms\Components\TextInput::make('branch_name')
                        ->label('Cabang')
                        ->placeholder('Ketik nama cabang')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, ?User $record) {
                            if ($record && $record->branch) {
                                $component->state($record->branch->name);
                            }
                        })
                        ->saveRelationshipsUsing(function (User $record, $state) {
                            $name = trim($state ?? '');
                            if (empty($name)) {
                                $record->branch_id = null;
                            } else {
                                $companyId = $record->company_id ?? \App\Models\Company::firstOrCreate(['name' => 'Default Company'])->id;
                                $branch = \App\Models\Branch::firstOrCreate([
                                    'company_id' => $companyId,
                                    'name' => $name,
                                ]);
                                $record->branch_id = $branch->id;
                            }
                            $record->save();
                        }),

                    Forms\Components\FileUpload::make('avatar')
                        ->label('Foto Profil')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('avatars')
                        ->visibility('public')
                        ->maxSize(2048),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->defaultImageUrl(fn (User $record) => $record->avatar_url)
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight(\Filament\Support\Enums\FontWeight::Medium),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (UserStatus $state): string => $state->color())
                    ->formatStateUsing(fn (UserStatus $state): string => $state->label()),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Login Terakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(UserStatus::options())
                    ->native(false),

                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Perusahaan')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('toggle_status')
                    ->label(fn (User $record): string => $record->status === UserStatus::Active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn (User $record): string => $record->status === UserStatus::Active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record): string => $record->status === UserStatus::Active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update([
                            'status' => $record->status === UserStatus::Active
                                ? UserStatus::Inactive
                                : UserStatus::Active,
                        ]);
                        Notification::make()
                            ->title('Status sales diperbarui')
                            ->success()
                            ->send();
                    }),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles', fn ($q) => $q->where('name', UserRole::Sales->value))
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->hasRole(UserRole::Admin->value));
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'view'   => Pages\ViewSales::route('/{record}'),
            'edit'   => Pages\EditSales::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
