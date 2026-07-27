<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\CompanySetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class CompanySettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan System';

    protected static ?string $navigationLabel = 'Pengaturan Perusahaan';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.company-settings';

    // ─────────────────────────────────────────────────────────────────────────
    // Access
    // ─────────────────────────────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // State
    // ─────────────────────────────────────────────────────────────────────────

    public ?array $companyData = [];
    public ?array $settingsData = [];

    public function mount(): void
    {
        $company = Company::first() ?? new Company();

        $this->companyForm->fill([
            'name'        => $company->name,
            'legal_name'  => $company->legal_name,
            'npwp'        => $company->npwp,
            'phone'       => $company->phone,
            'email'       => $company->email,
            'address'     => $company->address,
            'city'        => $company->city,
            'province'    => $company->province,
            'postal_code' => $company->postal_code,
            'country'     => $company->country ?? 'Indonesia',
            'website'     => $company->website,
            'logo'        => $company->logo,
        ]);

        $this->settingsForm->fill([
            'invoice_prefix'      => CompanySetting::get('invoice_prefix', 'INV'),
            'invoice_due_days'    => CompanySetting::get('invoice_due_days', 30),
            'low_stock_threshold' => CompanySetting::get('low_stock_threshold', 10),
            'tax_percentage'      => CompanySetting::get('tax_percentage', 11),
            'currency'            => CompanySetting::get('currency', 'IDR'),
            'timezone'            => CompanySetting::get('timezone', 'Asia/Jakarta'),
            'payment_methods'     => CompanySetting::get('payment_methods', 'Transfer Bank, Tunai'),
            'order_auto_confirm'  => CompanySetting::get('order_auto_confirm', false),
            'send_invoice_email'  => CompanySetting::get('send_invoice_email', true),
            'maintenance_mode'    => CompanySetting::get('maintenance_mode', false),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Forms — Filament v4: method name = form key, receives Schema
    // ─────────────────────────────────────────────────────────────────────────

    public function companyForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('companyData')
            ->components([
                Schemas\Components\Tabs::make('Pengaturan Perusahaan')
                    ->tabs([
                        Schemas\Components\Tabs\Tab::make('Identitas')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Schemas\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nama Perusahaan')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('legal_name')
                                        ->label('Nama Legal / PT')
                                        ->maxLength(255)
                                        ->placeholder('PT. Nama Perusahaan Indonesia'),

                                    Forms\Components\TextInput::make('npwp')
                                        ->label('NPWP')
                                        ->maxLength(30)
                                        ->placeholder('00.000.000.0-000.000'),

                                    Forms\Components\TextInput::make('phone')
                                        ->label('No. Telepon')
                                        ->tel()
                                        ->maxLength(20),

                                    Forms\Components\TextInput::make('email')
                                        ->label('Email Perusahaan')
                                        ->email()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('website')
                                        ->label('Website')
                                        ->url()
                                        ->maxLength(255)
                                        ->placeholder('https://www.example.com'),
                                ]),
                            ]),

                        Schemas\Components\Tabs\Tab::make('Alamat')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Textarea::make('address')
                                    ->label('Alamat Lengkap')
                                    ->rows(3)
                                    ->maxLength(500),

                                Schemas\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('city')
                                        ->label('Kota')
                                        ->maxLength(100),

                                    Forms\Components\TextInput::make('province')
                                        ->label('Provinsi')
                                        ->maxLength(100),

                                    Forms\Components\TextInput::make('postal_code')
                                        ->label('Kode Pos')
                                        ->maxLength(10),

                                    Forms\Components\TextInput::make('country')
                                        ->label('Negara')
                                        ->maxLength(100)
                                        ->default('Indonesia'),
                                ]),
                            ]),

                        Schemas\Components\Tabs\Tab::make('Logo')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->label('Logo Perusahaan')
                                    ->image()
                                    ->imageEditor()
                                    ->disk('public')
                                    ->directory('company')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->helperText('Ukuran maksimal 2MB. Format: JPG, PNG, SVG. Disarankan ukuran minimal 200x200px.')
                                    ->imagePreviewHeight('150'),
                            ]),
                    ]),
            ]);
    }

    public function settingsForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('settingsData')
            ->components([
                Schemas\Components\Tabs::make('Pengaturan Aplikasi')
                    ->tabs([
                        Schemas\Components\Tabs\Tab::make('Keuangan')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Schemas\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('invoice_prefix')
                                        ->label('Prefix Invoice')
                                        ->maxLength(20)
                                        ->placeholder('INV')
                                        ->helperText('Contoh: INV → INV-2024-0001'),

                                    Forms\Components\TextInput::make('invoice_due_days')
                                        ->label('Jatuh Tempo Invoice (hari)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(365)
                                        ->suffix('hari'),

                                    Forms\Components\TextInput::make('tax_percentage')
                                        ->label('Persentase PPN')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%')
                                        ->helperText('PPN saat ini 11%'),

                                    Forms\Components\TextInput::make('currency')
                                        ->label('Mata Uang')
                                        ->maxLength(10)
                                        ->placeholder('IDR'),

                                    Forms\Components\Textarea::make('payment_methods')
                                        ->label('Metode Pembayaran')
                                        ->rows(2)
                                        ->columnSpanFull()
                                        ->helperText('Pisahkan dengan koma. Contoh: Transfer Bank, Tunai, Cek'),
                                ]),
                            ]),

                        Schemas\Components\Tabs\Tab::make('Stok & Order')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Schemas\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('low_stock_threshold')
                                        ->label('Ambang Batas Stok Rendah')
                                        ->numeric()
                                        ->minValue(0)
                                        ->suffix('unit')
                                        ->helperText('Alert akan muncul jika stok di bawah jumlah ini'),

                                    Forms\Components\Toggle::make('order_auto_confirm')
                                        ->label('Konfirmasi Order Otomatis')
                                        ->helperText('Jika aktif, order baru langsung terkonfirmasi tanpa perlu review manual'),
                                ]),
                            ]),

                        Schemas\Components\Tabs\Tab::make('Notifikasi')
                            ->icon('heroicon-o-bell')
                            ->schema([
                                Schemas\Components\Grid::make(1)->schema([
                                    Forms\Components\Toggle::make('send_invoice_email')
                                        ->label('Kirim Email Invoice ke Customer')
                                        ->helperText('Otomatis kirim invoice via email saat invoice dibuat'),
                                ]),
                            ]),

                        Schemas\Components\Tabs\Tab::make('Sistem')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Schemas\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('timezone')
                                        ->label('Zona Waktu')
                                        ->options([
                                            'Asia/Jakarta'  => 'WIB - Asia/Jakarta (UTC+7)',
                                            'Asia/Makassar' => 'WITA - Asia/Makassar (UTC+8)',
                                            'Asia/Jayapura' => 'WIT - Asia/Jayapura (UTC+9)',
                                        ])
                                        ->native(false),

                                    Forms\Components\Toggle::make('maintenance_mode')
                                        ->label('Mode Maintenance')
                                        ->helperText('Aktifkan untuk mencegah akses user selain superadmin')
                                        ->onColor('danger')
                                        ->offColor('success'),
                                ]),
                            ]),
                    ]),
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Save actions
    // ─────────────────────────────────────────────────────────────────────────

    public function saveCompany(): void
    {
        $data = $this->companyForm->getState();

        $company = Company::first() ?? new Company(['name' => $data['name']]);
        $company->fill(array_merge($data, ['updated_by' => auth()->id()]));
        $company->save();

        Cache::forget('company_profile');

        Notification::make()
            ->title('Data perusahaan berhasil disimpan')
            ->success()
            ->send();
    }

    public function saveSettings(): void
    {
        $data = $this->settingsForm->getState();

        $typeMap = [
            'invoice_prefix'      => 'string',
            'invoice_due_days'    => 'integer',
            'low_stock_threshold' => 'integer',
            'tax_percentage'      => 'integer',
            'currency'            => 'string',
            'timezone'            => 'string',
            'payment_methods'     => 'string',
            'order_auto_confirm'  => 'boolean',
            'send_invoice_email'  => 'boolean',
            'maintenance_mode'    => 'boolean',
        ];

        foreach ($data as $key => $value) {
            CompanySetting::set($key, $value, $typeMap[$key] ?? 'string');
        }

        Notification::make()
            ->title('Pengaturan aplikasi berhasil disimpan')
            ->success()
            ->send();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Header actions
    // ─────────────────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveCompany')
                ->label('Simpan Data Perusahaan')
                ->icon('heroicon-o-building-office-2')
                ->color('primary')
                ->action('saveCompany'),

            Action::make('saveSettings')
                ->label('Simpan Pengaturan Aplikasi')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('success')
                ->action('saveSettings'),
        ];
    }

    public function getTitle(): string
    {
        return 'Pengaturan Perusahaan & Aplikasi';
    }
}
