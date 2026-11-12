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
        ->and($order->items)->toHaveCount(1)
        ->and((float) $order->total_amount)->toBe(3 * (float) $product->sell_price);
});

test('order cannot be created without choosing a customer', function () {
    $this->actingAs(User::where('email', 'sales@gabus.test')->first());

    Livewire::test(CreateOrder::class)
        ->fillForm(['customer_id' => null])
        ->call('create')
        ->assertHasFormErrors(['customer_id' => 'required']);
});

test('choosing status Sales Order Dibuat creates the sales order, invoice and receivable', function () {
    $sales = User::where('email', 'sales@gabus.test')->first();
    $customer = Customer::where('code', 'CUST-001')->first();
    $product = Product::where('sku', 'GAS-12KG')->first();

    $this->actingAs($sales);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'status' => OrderStatus::SOCreated->value,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => (float) $product->sell_price,
                'subtotal' => 2 * (float) $product->sell_price,
            ]],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $order = \App\Models\Order::first();
    $so = \App\Models\SalesOrder::where('order_id', $order->id)->first();

    expect($order->status)->toBe(OrderStatus::SOCreated)
        ->and($so)->not->toBeNull()
        ->and((float) $so->total_amount)->toBe(2 * (float) $product->sell_price)
        ->and(\App\Models\Invoice::where('sales_order_id', $so->id)->exists())->toBeTrue()
        ->and((float) $customer->fresh()->piutang_balance)->toBe(2 * (float) $product->sell_price);
});

test('other statuses are saved as chosen and do not create a sales order', function () {
    $sales = User::where('email', 'sales@gabus.test')->first();
    $customer = Customer::where('code', 'CUST-001')->first();
    $product = Product::where('sku', 'GAS-12KG')->first();

    $this->actingAs($sales);

    Livewire::test(CreateOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'status' => OrderStatus::SalesReviewed->value,
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => (float) $product->sell_price,
                'subtotal' => (float) $product->sell_price,
            ]],
        ])
        ->call('create');

    expect(\App\Models\Order::first()->status)->toBe(OrderStatus::SalesReviewed)
        ->and(\App\Models\SalesOrder::count())->toBe(0);
});
