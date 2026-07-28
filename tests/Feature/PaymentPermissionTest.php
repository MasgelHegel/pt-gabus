<?php

declare(strict_types=1);

use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\SalesOrderResource;
use App\Filament\Widgets\PendingPaymentsWidget;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('payment resource page access control', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // Sales CAN access PaymentResource (since permissions restored)
    $sales = User::where('email', 'sales@gabus.test')->first();
    $this->actingAs($sales);
    expect(PaymentResource::canAccess())->toBeTrue();

    // Admin cannot access PaymentResource
    $admin = User::role(UserRole::Admin->value)->first();
    $this->actingAs($admin);
    expect(PaymentResource::canAccess())->toBeFalse();

    // Accounting can access PaymentResource
    $accountingUser = User::create([
        'name' => 'Accounting User',
        'email' => 'accounting_test@gabus.test',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);
    $accountingUser->assignRole(UserRole::Accounting->value);
    $this->actingAs($accountingUser);
    expect(PaymentResource::canAccess())->toBeTrue();

    // SuperAdmin can access PaymentResource
    $superadmin = User::role(UserRole::SuperAdmin->value)->first();
    $this->actingAs($superadmin);
    expect(PaymentResource::canAccess())->toBeTrue();
});

test('PendingPaymentsWidget visibility control', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $sales = User::where('email', 'sales@gabus.test')->first();
    $admin = User::role(UserRole::Admin->value)->first();
    $superadmin = User::role(UserRole::SuperAdmin->value)->first();

    $accountingUser = User::create([
        'name' => 'Accounting User',
        'email' => 'accounting_test@gabus.test',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);
    $accountingUser->assignRole(UserRole::Accounting->value);

    // Sales can see the widget (restored)
    $this->actingAs($sales);
    expect(PendingPaymentsWidget::canView())->toBeTrue();

    // Admin can see the widget (restored to original)
    $this->actingAs($admin);
    expect(PendingPaymentsWidget::canView())->toBeTrue();

    // Accounting can see the widget
    $this->actingAs($accountingUser);
    expect(PendingPaymentsWidget::canView())->toBeTrue();

    // SuperAdmin can see the widget
    $this->actingAs($superadmin);
    expect(PendingPaymentsWidget::canView())->toBeTrue();
});

test('order and sales order resource access control for admin and accounting', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $admin = User::role(UserRole::Admin->value)->first();
    $superadmin = User::role(UserRole::SuperAdmin->value)->first();

    $accountingUser = User::create([
        'name' => 'Accounting User',
        'email' => 'accounting_test@gabus.test',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);
    $accountingUser->assignRole(UserRole::Accounting->value);

    // 1. Admin CANNOT access OrderResource or SalesOrderResource
    $this->actingAs($admin);
    expect(OrderResource::canAccess())->toBeFalse()
        ->and(SalesOrderResource::canAccess())->toBeFalse();

    // 2. Accounting CAN access OrderResource and SalesOrderResource
    $this->actingAs($accountingUser);
    expect(OrderResource::canAccess())->toBeTrue()
        ->and(SalesOrderResource::canAccess())->toBeTrue();

    // 3. SuperAdmin CAN access OrderResource and SalesOrderResource
    $this->actingAs($superadmin);
    expect(OrderResource::canAccess())->toBeTrue()
        ->and(SalesOrderResource::canAccess())->toBeTrue();
});

test('LaporanPenjualan page loads successfully for authorized users', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $superadmin = User::role(UserRole::SuperAdmin->value)->first();
    $this->actingAs($superadmin);

    $response = $this->get(\App\Filament\Pages\LaporanPenjualan::getUrl());
    $response->assertStatus(200);
});
