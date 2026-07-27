<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('admin and superadmin can delete order, others cannot', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $superadmin = User::role(UserRole::SuperAdmin->value)->first();
    $admin = User::role(UserRole::Admin->value)->first();
    $sales = User::where('email', 'sales@gabus.test')->first();
    $customer = User::where('email', 'customer@gabus.test')->first();

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

    // actingAs admin
    $this->actingAs($admin);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeTrue();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeTrue();

    // actingAs sales
    $this->actingAs($sales);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeFalse();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeFalse();

    // actingAs customer
    $this->actingAs($customer);
    expect(App\Filament\Resources\OrderResource::canDelete($order))->toBeFalse();
    expect(App\Filament\Resources\OrderResource::canDeleteAny())->toBeFalse();
});

test('admin can delete order through order resource list page', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $admin = User::role(UserRole::Admin->value)->first();
    $order = Order::create([
        'order_number' => 'ORD-TEST-2',
        'customer_id' => Customer::first()->id,
        'status' => OrderStatus::Draft,
        'total_amount' => 1000,
    ]);

    Livewire\Livewire::actingAs($admin)
        ->test(App\Filament\Resources\OrderResource\Pages\ListOrders::class)
        ->callTableAction('delete', $order);

    $this->assertSoftDeleted('orders', ['id' => $order->id]);
});
