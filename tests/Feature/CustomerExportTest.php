<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Exports\CustomersExport;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('customers export has correct headings and maps customer data accurately', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $user = User::factory()->create([
        'name'  => 'Portal User',
        'email' => 'portal@example.com',
    ]);

    $customer = Customer::create([
        'user_id'         => $user->id,
        'code'            => 'CST-9999',
        'name'            => 'PT Sumber Makmur',
        'company_name'    => 'Sumber Makmur Jaya',
        'email'           => 'info@sumbermakmur.com',
        'phone'           => '08123456789',
        'address'         => 'Jl. Industri No. 10, Jakarta',
        'credit_limit'    => 50000000,
        'piutang_balance' => 12500000,
        'due_period_days' => 45,
    ]);

    $export = new CustomersExport();

    $headings = $export->headings();
    expect($headings)->toContain(
        'Kode Customer',
        'Nama Customer',
        'Nama Perusahaan',
        'Email',
        'No. Telepon',
        'Alamat',
        'Batas Kredit (Rp)',
        'Saldo Piutang (Rp)',
        'TOP (Hari)',
        'Status Akun Portal',
        'Email Akun Portal',
        'Tanggal Terdaftar'
    );

    $customer->load('user');
    $mapped = $export->map($customer);

    expect($mapped[0])->toBe('CST-9999')
        ->and($mapped[1])->toBe('PT Sumber Makmur')
        ->and($mapped[2])->toBe('Sumber Makmur Jaya')
        ->and($mapped[3])->toBe('info@sumbermakmur.com')
        ->and($mapped[4])->toBe('08123456789')
        ->and($mapped[5])->toBe('Jl. Industri No. 10, Jakarta')
        ->and($mapped[6])->toBe(50000000.0)
        ->and($mapped[7])->toBe(12500000.0)
        ->and($mapped[8])->toBe(45)
        ->and($mapped[9])->toBe('Aktif')
        ->and($mapped[10])->toBe('portal@example.com');
});

test('admin and superadmin can see and trigger export excel action on customer list page', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    Excel::fake();

    $admin = User::role(UserRole::Admin->value)->first();
    $superadmin = User::role(UserRole::SuperAdmin->value)->first();

    // Admin can see and call export_excel
    Livewire::actingAs($admin)
        ->test(ListCustomers::class)
        ->assertActionVisible('export_excel')
        ->callAction('export_excel');

    Excel::assertDownloaded('data-customer-' . now()->format('Ymd') . '.xlsx', function (CustomersExport $export) {
        return true;
    });

    // Super Admin can see and call export_excel
    Livewire::actingAs($superadmin)
        ->test(ListCustomers::class)
        ->assertActionVisible('export_excel')
        ->callAction('export_excel');

    Excel::assertDownloaded('data-customer-' . now()->format('Ymd') . '.xlsx', function (CustomersExport $export) {
        return true;
    });
});

test('non-admin roles cannot see export excel action on customer list page', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $sales = User::where('email', 'sales@gabus.test')->first();

    // Sales can access customer page (has view-customers), but cannot see export_excel action
    Livewire::actingAs($sales)
        ->test(ListCustomers::class)
        ->assertActionHidden('export_excel');
});

test('admin can trigger export_selected bulk action', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    Excel::fake();

    $admin = User::role(UserRole::Admin->value)->first();
    $customers = Customer::limit(2)->get();

    Livewire::actingAs($admin)
        ->test(ListCustomers::class)
        ->callTableBulkAction('export_selected', $customers);

    Excel::assertDownloaded('data-customer-terpilih-' . now()->format('Ymd') . '.xlsx', function (CustomersExport $export) {
        return true;
    });
});

