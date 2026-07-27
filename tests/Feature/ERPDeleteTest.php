<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('deleting sales order reverts all invoices, payments, journals, and account balances', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $customerUser = User::where('email', 'customer@gabus.test')->first();
    $salesUser    = User::where('email', 'sales@gabus.test')->first();
    $adminUser    = User::role(UserRole::Admin->value)->first();
    $customer     = Customer::where('user_id', $customerUser->id)->first();
    $product      = \App\Models\Product::first();

    /** @var OrderWorkflowService $service */
    $service = app(OrderWorkflowService::class);

    // 1. Create and review order
    $order = $service->createCustomerOrder($customer->id, [
        ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1000000],
    ]);
    $order = $service->salesReviewOrder($order, $salesUser->id);

    // 2. Approve order (SO + Invoice + Piutang + Journal)
    $so = $service->adminApproveOrder($order, $adminUser->id);

    $invoice = $so->invoice;
    expect($invoice)->not->toBeNull();

    // Check account balances are updated
    $receivableAccount = Account::where('code', '1120')->first();
    $revenueAccount    = Account::where('code', '4100')->first();

    expect((float) $receivableAccount->balance)->toBe(2000000.0);
    expect((float) $revenueAccount->balance)->toBe(2000000.0);
    expect((float) $customer->fresh()->piutang_balance)->toBe(2000000.0);

    // Verify journal entry exists
    $journalEntry = JournalEntry::where('reference', $invoice->invoice_number)->first();
    expect($journalEntry)->not->toBeNull();

    // 3. Delete Sales Order
    $so->delete();

    // 4. Verify everything is reverted
    expect(Invoice::where('id', $invoice->id)->exists())->toBeFalse();
    expect(JournalEntry::where('reference', $invoice->invoice_number)->exists())->toBeFalse();

    // Accounts balance should be 0
    expect((float) $receivableAccount->fresh()->balance)->toBe(0.0);
    expect((float) $revenueAccount->fresh()->balance)->toBe(0.0);

    // Customer piutang should be 0
    expect((float) $customer->fresh()->piutang_balance)->toBe(0.0);

    // Order status should be reverted
    expect($order->fresh()->status)->toBe(OrderStatus::SalesReviewed);
});

test('deleting payment reverts verified payment, cash account, and customer piutang', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $customerUser = User::where('email', 'customer@gabus.test')->first();
    $salesUser    = User::where('email', 'sales@gabus.test')->first();
    $adminUser    = User::role(UserRole::Admin->value)->first();
    $customer     = Customer::where('user_id', $customerUser->id)->first();
    $product      = \App\Models\Product::first();

    /** @var OrderWorkflowService $service */
    $service = app(OrderWorkflowService::class);

    // 1. Create, review, approve order
    $order = $service->createCustomerOrder($customer->id, [
        ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1000000],
    ]);
    $order = $service->salesReviewOrder($order, $salesUser->id);
    $so = $service->adminApproveOrder($order, $adminUser->id);

    // 2. Upload payment
    $payment = $service->uploadPaymentProof($so->invoice, 'payments/test.jpg', 2000000.0);

    // 3. Verify payment
    $cashAccount = Account::where('is_cash_bank', true)->first();
    $payment = $service->verifyPayment($payment, true, $adminUser->id, $cashAccount->id);

    $receivableAccount = Account::where('code', '1120')->first();

    // Check balances
    expect((float) $cashAccount->fresh()->balance)->toBe(2000000.0);
    expect((float) $receivableAccount->fresh()->balance)->toBe(0.0); // Piutang paid off (2M debit from invoice - 2M credit from payment)
    expect((float) $customer->fresh()->piutang_balance)->toBe(0.0);

    // 4. Delete payment
    $payment->delete();

    // 5. Verify balances are reverted
    expect((float) $cashAccount->fresh()->balance)->toBe(0.0);
    expect((float) $receivableAccount->fresh()->balance)->toBe(2000000.0); // Piutang goes back to 2M
    expect((float) $customer->fresh()->piutang_balance)->toBe(2000000.0);
});
