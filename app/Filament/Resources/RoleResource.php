<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\RoleResource\Pages;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan System';

    protected static ?int $navigationSort = 10;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $modelLabel = 'Peran';

    protected static ?string $pluralModelLabel = 'Manajemen Peran & Izin';

    protected static ?string $recordTitleAttribute = 'name';

    // ─────────────────────────────────────────────────────────────────────────
    // Access
    // ─────────────────────────────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Form
    // ─────────────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        $permissionGroups = Permission::all()
            ->groupBy(fn (Permission $p) => explode('-', $p->name)[1] ?? 'general')
            ->map(fn ($perms, $group) => $perms->pluck('name', 'name'));

        return $schema->components([
            Section::make('Informasi Peran')
                ->description('Nama unik yang mengidentifikasi peran ini di sistem.')
                ->icon('heroicon-o-identification')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Peran')
                        ->required()
                        ->unique(Role::class, 'name', ignoreRecord: true)
                        ->maxLength(125)
                        ->placeholder('contoh: admin, sales, cashier')
                        ->helperText('Gunakan huruf kecil dan tanda hubung. Contoh: super_admin')
                        ->disabled(fn (?Role $record): bool => $record !== null && in_array(
                            $record->name,
                            array_column(UserRole::cases(), 'value'),
                            true
                        )),

                    Forms\Components\TextInput::make('guard_name')
                        ->label('Guard')
                        ->default('web')
                        ->required()
                        ->maxLength(125)
                        ->disabled(),
                ]),

            Section::make('Izin Akses')
                ->description('Pilih izin yang diberikan kepada peran ini. Izin dikelompokkan per modul.')
                ->icon('heroicon-o-key')
                ->schema([
                    Forms\Components\CheckboxList::make('permissions')
                        ->label('')
                        ->relationship('permissions', 'name')
                        ->options(Permission::orderBy('name')->pluck('name', 'name'))
                        ->columns(3)
                        ->gridDirection('row')
                        ->searchable()
                        ->bulkToggleable(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Peran')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (Role $record): string => match($record->name) {
                        UserRole::SuperAdmin->value => 'danger',
                        UserRole::Admin->value      => 'warning',
                        UserRole::Sales->value      => 'info',
                        UserRole::Customer->value   => 'success',
                        UserRole::Manager->value    => 'primary',
                        UserRole::Cashier->value    => 'success',
                        default                     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Jumlah Izin')
                    ->counts('permissions')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Jumlah User')
                    ->counts('users')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options(['web' => 'web', 'api' => 'api', 'sanctum' => 'sanctum'])
                    ->native(false),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()
                    ->visible(fn (Role $record): bool => ! (
                        $record->name === UserRole::SuperAdmin->value
                        && ! auth()->user()?->isSuperAdmin()
                    )),
                Actions\Action::make('users')
                    ->label('Lihat User')
                    ->icon('heroicon-o-users')
                    ->color('gray')
                    ->url(fn (Role $record): string => route('filament.admin.resources.users.index', [
                        'tableFilters[roles][value]' => $record->id,
                    ])),
                Actions\DeleteAction::make()
                    ->before(function (Role $record, Actions\DeleteAction $action): void {
                        if (in_array($record->name, array_column(UserRole::cases(), 'value'), true)) {
                            Notification::make()
                                ->title('Tidak dapat menghapus peran bawaan sistem')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->striped()
            ->emptyStateIcon('heroicon-o-shield-exclamation')
            ->emptyStateHeading('Belum ada peran')
            ->emptyStateDescription('Buat peran baru dan assign izin akses ke dalamnya.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pages
    // ─────────────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view'   => Pages\ViewRole::route('/{record}'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Role::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
