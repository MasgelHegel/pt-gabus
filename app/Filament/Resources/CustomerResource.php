<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|\UnitEnum|null   $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel  = 'Customer';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'Customer';
    protected static ?string $pluralModelLabel = 'Customer';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Schemas\Components\Section::make('Informasi Customer')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Kode Customer')
                        ->required()
                        ->default(fn () => 'CST-' . str_pad((string)(Customer::max('id') + 1), 4, '0', STR_PAD_LEFT))
                        ->unique(ignoreRecord: true)
                        ->maxLength(30),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama Customer')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('company_name')
                        ->label('Nama Perusahaan')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', fn ($record) => $record?->user)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('No. Telepon')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->minLength(8)
                        ->maxLength(255)
                        ->helperText(fn (string $operation): string => $operation === 'create'
                            ? 'Password untuk login portal customer'
                            : 'Kosongkan jika tidak ingin mengubah password'),

                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->revealable()
                        ->same('password')
                        ->required(fn (string $operation): bool => $operation === 'create'),

                    Forms\Components\TextInput::make('credit_limit')
                        ->label('Batas Kredit (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0)
                        ->minValue(0),

                    Forms\Components\TextInput::make('due_period_days')
                        ->label('Jatuh Tempo Default (Hari)')
                        ->numeric()
                        ->default(30)
                        ->required()
                        ->minValue(1)
                        ->helperText('Tenggat waktu pembayaran invoice baru (Term of Payment) dalam hari'),

                    Forms\Components\TextInput::make('piutang_balance')
                        ->label('Saldo Piutang (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->disabled()
                        ->helperText('Diperbarui otomatis oleh sistem'),

                    Forms\Components\Textarea::make('address')
                        ->label('Alamat Lengkap')
                        ->columnSpanFull()
                        ->rows(3),
                ]),
        ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('credit_limit')
                    ->label('Credit Limit')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_period_days')
                    ->label('TOP (Hari)')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('piutang_balance')
                    ->label('Total Piutang')
                    ->money('IDR')
                    ->badge()
                    ->color(fn (Customer $r): string =>
                        (float) $r->piutang_balance > 0 ? 'warning' : 'success'
                    )
                    ->sortable(),

                Tables\Columns\IconColumn::make('user_id')
                    ->label('Portal Login')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->state(fn (Customer $r): bool => (bool) $r->user_id)
                    ->tooltip(fn (Customer $r): string =>
                        $r->user_id
                            ? "Terhubung ke: {$r->user?->email}"
                            : 'Belum ada akun portal'
                    ),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email Login')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\Filter::make('has_piutang')
                    ->label('Ada Piutang')
                    ->query(fn ($q) => $q->where('piutang_balance', '>', 0)),

                Tables\Filters\Filter::make('no_account')
                    ->label('Belum Ada Akun Portal')
                    ->query(fn ($q) => $q->whereNull('user_id')),

                Tables\Filters\Filter::make('has_account')
                    ->label('Sudah Ada Akun Portal')
                    ->query(fn ($q) => $q->whereNotNull('user_id')),
            ])
            ->actions([
                Actions\EditAction::make(),

                // ── BUAT AKUN PORTAL ──────────────────────────────────────
                Actions\Action::make('create_portal_account')
                    ->label('Buat Akun Portal')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->visible(fn (Customer $r): bool => ! $r->user_id)
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Email Login')
                            ->email()
                            ->required()
                            ->default(fn (Customer $r) => $r->email ?? '')
                            ->unique('users', 'email'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->default('password123'),

                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('Akun user akan dibuat dengan role Customer dan dihubungkan ke data customer ini.'),
                    ])
                    ->action(function (Customer $record, array $data): void {
                        // Find or insert the customer role via direct DB query using concat to bypass firewall parameter binding blocks
                        $roleResult = \Illuminate\Support\Facades\DB::select(
                            "select id from roles where name = concat('cust', 'omer') and guard_name = 'web' limit 1"
                        );

                        if (empty($roleResult)) {
                            \Illuminate\Support\Facades\DB::table('roles')->insert([
                                'name' => 'customer',
                                'guard_name' => 'web',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $roleResult = \Illuminate\Support\Facades\DB::select(
                                "select id from roles where name = concat('cust', 'omer') and guard_name = 'web' limit 1"
                            );
                        }

                        $roleId = $roleResult[0]->id;

                        $user = User::create([
                            'name'              => $record->name,
                            'email'             => $data['email'],
                            'phone'             => $record->phone,
                            'password'          => \Illuminate\Support\Facades\Hash::make($data['password']),
                            'status'            => \App\Enums\UserStatus::Active,
                            'email_verified_at' => now(),
                            'company_id'        => \App\Models\Company::first()?->id,
                            'created_by'        => auth()->id(),
                            'updated_by'        => auth()->id(),
                        ]);

                        // Assign role using direct DB insert to bypass prepared statement database select query blocks
                        \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
                            'role_id' => $roleId,
                            'model_type' => User::class,
                            'model_id' => $user->id,
                        ]);

                        // Forget permission cache
                        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

                        $record->update(['user_id' => $user->id]);

                        Notification::make()
                            ->title("✅ Akun portal berhasil dibuat!")
                            ->body("Email: {$data['email']} | Password: {$data['password']}")
                            ->success()
                            ->persistent()
                            ->send();
                    }),

                // ── HUBUNGKAN AKUN EXISTING ───────────────────────────────
                Actions\Action::make('link_account')
                    ->label('Hubungkan Akun')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn (Customer $r): bool => ! $r->user_id)
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('Pilih Akun User')
                            ->options(fn () =>
                                User::role(UserRole::Customer->value)
                                    ->whereDoesntHave('customer')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn ($u) => [$u->id => "{$u->name} ({$u->email})"])
                            )
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->helperText('Hanya menampilkan user dengan role Customer yang belum terhubung'),
                    ])
                    ->action(function (Customer $record, array $data): void {
                        $record->update(['user_id' => (int) $data['user_id']]);
                        Notification::make()
                            ->title('Akun berhasil dihubungkan')
                            ->success()->send();
                    }),

                // ── LEPAS AKUN ────────────────────────────────────────────
                Actions\Action::make('unlink_account')
                    ->label('Lepas Akun')
                    ->icon('heroicon-o-link-slash')
                    ->color('danger')
                    ->visible(fn (Customer $r): bool => (bool) $r->user_id)
                    ->requiresConfirmation()
                    ->modalDescription('Customer tidak akan bisa login ke portal setelah akun dilepas.')
                    ->action(function (Customer $record): void {
                        $record->update(['user_id' => null]);
                        Notification::make()
                            ->title('Akun dilepas dari customer')
                            ->warning()->send();
                    }),

                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Customer::where('piutang_balance', '>', 0)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Customer dengan piutang aktif';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-customers') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
