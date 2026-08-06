<?php

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\QCStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('full erp workflow from customer order to payment verification and auto journal', function () {
    // 1. Seed Roles & Users
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $customerUser = User::where('email', 'customer@gabus.test')->first();
    $salesUser    = User::where('email', 'sales@gabus.test')->first();
    $adminUser    = User::role(\App\Enums\UserRole::Admin->value)->first();
    $customer     = Customer::where('user_id', $customerUser->id)->first();
    $product      = Product::first();
    $supplier     = Supplier::first();
    $warehouse    = Warehouse::first();

    /** @var OrderWorkflowService $service */
    $service = app(OrderWorkflowService::class);

    // STEP 1: Customer Membuat Order
    $order = $service->createCustomerOrder($customer->id, [
        ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 2000000],
    ], 'Tolong diproses cepat');

    expect($order->status)->toBe(OrderStatus::Submitted);
    expect($order->total_amount)->toBe('4000000.00');

    // STEP 2: Sales Menerima & Review Order
    $order = $service->salesReviewOrder($order, $salesUser->id);
    expect($order->status)->toBe(OrderStatus::SalesReviewed);

    // STEP 3: Admin Approval & SO Generation + Invoice & Piutang
    $so = $service->adminApproveOrder($order, $adminUser->id, 0.0);
    expect($order->fresh()->status)->toBe(OrderStatus::SOCreated);
    expect($so->status)->toBe(SalesOrderStatus::Processing);
    expect($so->invoice)->not->toBeNull();
    expect($so->invoice->status)->toBe(InvoiceStatus::Unpaid);

    // Check Piutang Customer Bertambah
    expect((float) $customer->fresh()->piutang_balance)->toBe(4000000.0);

    // STEP 4: Purchase Order ke Supplier
    $po = $service->createPurchaseOrder($so, $supplier->id);
    expect($po->status)->toBe(PurchaseOrderStatus::Ordered);

    // STEP 5: Barang Masuk (Goods Received) & QC
    $receipt = $service->processGoodsReceipt($po, $warehouse->id, $adminUser->id);
    expect($po->fresh()->status)->toBe(PurchaseOrderStatus::GoodsReceived);

    $qc = $service->verifyQCCheck($receipt, true, $adminUser->id);
    expect($qc->status)->toBe(QCStatus::Passed);

    // STEP 6: Packing & Pengiriman (Shipment)
    $shipment = $service->shipSalesOrder($so, 'JNE Express', 'RESI-999888');
    expect($so->fresh()->status)->toBe(SalesOrderStatus::Shipped);

    // STEP 7: Customer Menerima Barang
    $service->markShipmentDelivered($shipment);
    expect($so->fresh()->status)->toBe(SalesOrderStatus::Delivered);

    // STEP 8: Customer Upload Pembayaran
    $payment = $service->uploadPaymentProof($so->invoice, 'payments/demo-proof.jpg', 4000000.0);
    expect($so->invoice->fresh()->status)->toBe(InvoiceStatus::PaymentUploaded);

    // STEP 9: Admin Verifikasi Pembayaran (Kas Bertambah, Piutang Berkurang, Jurnal Otomatis)
    $payment = $service->verifyPayment($payment, true, $adminUser->id);

    expect($payment->status)->toBe(PaymentStatus::Verified);
    expect($so->invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
    expect((float) $customer->fresh()->piutang_balance)->toBe(0.0);
});

test('invoice due date is generated based on customer due period days', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $customerUser = User::where('email', 'customer@gabus.test')->first();
    $adminUser    = User::role(\App\Enums\UserRole::Admin->value)->first();
    $customer     = Customer::where('user_id', $customerUser->id)->first();
    $product      = Product::first();

    // Set custom due period days for this customer
    $customer->update(['due_period_days' => 15]);

    /** @var OrderWorkflowService $service */
    $service = app(OrderWorkflowService::class);

    $order = $service->createCustomerOrder($customer->id, [
        ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100000],
    ]);

    // Approve the order (will auto-create invoice)
    $so = $service->adminApproveOrder($order, $adminUser->id);

    // Verify invoice due date matches custom due_period_days (15 days from now)
    $expectedDueDate = now()->addDays(15)->toDateString();
    expect($so->invoice->due_date->toDateString())->toBe($expectedDueDate);
});

test('invoice due date matches custom due date when provided during approval', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $customerUser = User::where('email', 'customer@gabus.test')->first();
    $adminUser    = User::role(\App\Enums\UserRole::Admin->value)->first();
    $customer     = Customer::where('user_id', $customerUser->id)->first();
    $product      = Product::first();

    /** @var OrderWorkflowService $service */
    $service = app(OrderWorkflowService::class);

    $order = $service->createCustomerOrder($customer->id, [
        ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 100000],
    ]);

    // Define custom due date
    $customDueDate = \Carbon\Carbon::parse('2026-12-25');

    // Approve the order (will auto-create invoice with custom due date)
    $so = $service->adminApproveOrder($order, $adminUser->id, 0.0, null, $customDueDate);

    // Verify invoice due date matches custom due date exactly
    expect($so->invoice->due_date->toDateString())->toBe('2026-12-25');
});
