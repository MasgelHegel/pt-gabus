<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use App\Enums\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('accounting and superadmin can delete order, others cannot', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $superadmin = User::role(UserRole::SuperAdmin->value)->first();
    $admin = User::role(UserRole::Admin->value)->first();
    $sales = User::where('email', 'sales@gabus.test')->first();
    $customer = User::where('email', 'customer@gabus.test')->first();

    $accounting = User::create([
        'name' => 'Accounting User',
        'email' => 'accounting_test@gabus.test',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);
    $accounting->assignRole(UserRole::Accounting->value);

    $order = Order::create([
        'order_number' => 'ORD-TEST-1',
        'customer_id' => Customer::first()->id,
        'status' => OrderStatus::Draft,
        'total_amount' => 1000,
    ]);

    // Guest / no user logged in
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeFalse();

    // actingAs superadmin
    $this->actingAs($superadmin);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeTrue();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeTrue();

    // actingAs accounting
    $this->actingAs($accounting);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeTrue();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeTrue();

    // actingAs admin (does not have access anymore)
    $this->actingAs($admin);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeFalse();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeFalse();

    // actingAs sales
    $this->actingAs($sales);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeFalse();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeFalse();

    // actingAs customer
    $this->actingAs($customer);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeFalse();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeFalse();
});

test('accounting can delete order through order resource list page', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $accounting = User::create([
        'name' => 'Accounting User',
        'email' => 'accounting_test@gabus.test',
        'password' => Hash::make('password'),
        'status' => UserStatus::Active,
    ]);
    $accounting->assignRole(UserRole::Accounting->value);

    $order = Order::create([
        'order_number' => 'ORD-TEST-2',
        'customer_id' => Customer::first()->id,
        'status' => OrderStatus::Draft,
        'total_amount' => 1000,
    ]);

    Livewire\Livewire::actingAs($accounting)
        ->test(App\Filament\Resources\OrderResource\Pages\ListOrders::class)
        ->callTableAction('delete', $order);

    $this->assertSoftDeleted('orders', ['id' => $order->id]);
});
