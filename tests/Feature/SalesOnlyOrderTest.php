<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

test('public order form and customer self-order routes are removed', function () {
    $this->get('/order')->assertNotFound();
    $this->post('/order', [])->assertNotFound();
    $this->get('/portal/orders/create')->assertNotFound();

    $customerUser = User::where('email', 'customer@gabus.test')->first();
    $this->actingAs($customerUser)->get('/portal/orders/create')->assertNotFound();
    $this->actingAs($customerUser)->post('/portal/orders', [])->assertStatus(405);
});

test('welcome page no longer shows the order button', function () {
    $this->get('/')->assertOk()->assertDontSee('Pesan Gas Sekarang');
});

test('only internal staff can create orders in the admin panel', function () {
    $sales = User::where('email', 'sales@gabus.test')->first();
    $customerUser = User::where('email', 'customer@gabus.test')->first();

    $this->actingAs($sales);
    expect(OrderResource::canCreate())->toBeTrue();

    $this->actingAs($customerUser);
    expect(OrderResource::canCreate())->toBeFalse();
});

test('sales creates an order and chooses which customer ordered', function () {
    $sales = User::where('email', 'sales@gabus.test')->first();
    $customer = Customer::where('code', 'CUST-001')->first();
    $product = Product::where('sku', 'GAS-12KG')->first();

    $this->actingAs($sales);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 3,
                'unit_price' => (float) $product->sell_price,
                'subtotal' => 3 * (float) $product->sell_price,
            ]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $order = \App\Models\Order::first();
    expect($order->customer_id)->toBe($customer->id)
        ->and($order->sales_id)->toBe($sales->id)
        ->and($order->status)->toBe(OrderStatus::Submitted)
        ->and($order->items)->toHaveCount(1);
});

test('order cannot be created without choosing a customer', function () {
    $this->actingAs(User::where('email', 'sales@gabus.test')->first());

    Livewire::test(CreateOrder::class)
        ->fillForm(['customer_id' => null])
        ->call('create')
        ->assertHasFormErrors(['customer_id' => 'required']);
});
