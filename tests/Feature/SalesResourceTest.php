<?php

declare(strict_types=1);

use App\Filament\Resources\SalesResource;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('superadmin can access sales resource pages', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $superadmin = User::role(UserRole::SuperAdmin->value)->first();

    $response = $this->actingAs($superadmin)->get(SalesResource::getUrl('index'));
    $response->assertStatus(200);
});

test('admin can access sales resource pages', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $admin = User::role(UserRole::Admin->value)->first();

    $response = $this->actingAs($admin)->get(SalesResource::getUrl('index'));
    $response->assertStatus(200);
});

test('sales cannot access sales resource pages', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $sales = User::where('email', 'sales@gabus.test')->first();

    $response = $this->actingAs($sales)->get(SalesResource::getUrl('index'));
    $response->assertStatus(403);
});

test('creating a sales user via livewire form auto-assigns sales role', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $admin = User::role(UserRole::Admin->value)->first();

    Livewire\Livewire::actingAs($admin)
        ->test(SalesResource\Pages\CreateSales::class)
        ->set('data.name', 'Sales Baru Test')
        ->set('data.email', 'salesbaru@gabus.test')
        ->set('data.phone', '0812345678')
        ->set('data.status', UserStatus::Active->value)
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->call('create')
        ->assertRedirect(SalesResource::getUrl('index'));

    $newUser = User::where('email', 'salesbaru@gabus.test')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->hasRole(UserRole::Sales->value))->toBeTrue();
    expect($newUser->canAccessPanel(Filament\Facades\Filament::getPanel('admin')))->toBeTrue();
});

test('sales representative can only view their own sales orders in SalesOrderResource', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $salesBudi = User::where('email', 'sales@gabus.test')->first();

    $salesHegel = User::create([
        'name' => 'Mochamad Hegel Test',
        'email' => 'hegeltest@gabus.test',
        'status' => UserStatus::Active,
        'password' => Hash::make('password'),
    ]);
    $salesHegel->assignRole(UserRole::Sales->value);

    $customer = \App\Models\Customer::first();

    $soBudi = \App\Models\SalesOrder::create([
        'so_number' => 'SO-BUDI-1',
        'customer_id' => $customer->id,
        'sales_id' => $salesBudi->id,
        'status' => \App\Enums\SalesOrderStatus::Processing,
        'subtotal' => 1000,
        'total_amount' => 1000,
    ]);

    $soHegel = \App\Models\SalesOrder::create([
        'so_number' => 'SO-HEGEL-1',
        'customer_id' => $customer->id,
        'sales_id' => $salesHegel->id,
        'status' => \App\Enums\SalesOrderStatus::Processing,
        'subtotal' => 1000,
        'total_amount' => 1000,
    ]);

    Livewire\Livewire::actingAs($salesBudi)
        ->test(App\Filament\Resources\SalesOrderResource\Pages\ListSalesOrders::class)
        ->assertCanSeeTableRecords([$soBudi])
        ->assertCanNotSeeTableRecords([$soHegel]);

    Livewire\Livewire::actingAs($salesHegel)
        ->test(App\Filament\Resources\SalesOrderResource\Pages\ListSalesOrders::class)
        ->assertCanSeeTableRecords([$soHegel])
        ->assertCanNotSeeTableRecords([$soBudi]);

    $admin = User::role(UserRole::Admin->value)->first();
    Livewire\Livewire::actingAs($admin)
        ->test(App\Filament\Resources\SalesOrderResource\Pages\ListSalesOrders::class)
        ->assertCanSeeTableRecords([$soBudi, $soHegel]);
});
