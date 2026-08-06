<?php

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createTestInvoice(): Invoice
{
    $customerUser = User::where('email', 'customer@gabus.test')->first();
    $salesUser    = User::where('email', 'sales@gabus.test')->first();
    $adminUser    = User::role(\App\Enums\UserRole::Admin->value)->first();
    $customer     = Customer::where('user_id', $customerUser->id)->first();
    $product      = Product::first();

    /** @var OrderWorkflowService $service */
    $service = app(OrderWorkflowService::class);

    $order = $service->createCustomerOrder($customer->id, [
        ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 200000],
    ], 'Tolong diproses cepat');

    $order = $service->salesReviewOrder($order, $salesUser->id);
    $so = $service->adminApproveOrder($order, $adminUser->id, 0.0);

    return $so->invoice;
}

test('invoice getBankInformation defaults to BCA and correct account details', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $invoice = createTestInvoice();
    $bankInfo = $invoice->getBankInformation();

    expect($bankInfo['bank_name'])->toBe('BCA');
    expect($bankInfo['account_no'])->toBe('8421573832');
    expect($bankInfo['account_name'])->toBe('Rizka Firlana');
});

test('invoice getBankInformation returns Mandiri when payment linked to Mandiri account', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $invoice = createTestInvoice();
    $customer = $invoice->customer;

    // Retrieve already seeded Mandiri account
    $mandiriAccount = Account::where('code', '1111')->firstOrFail();

    $payment = Payment::create([
        'payment_number' => 'PAY-TEST-001',
        'invoice_id' => $invoice->id,
        'customer_id' => $customer->id,
        'account_id' => $mandiriAccount->id,
        'amount' => 400000,
        'payment_date' => now(),
        'status' => \App\Enums\PaymentStatus::Verified->value,
    ]);

    $bankInfo = $invoice->getBankInformation();

    expect($bankInfo['bank_name'])->toBe('Mandiri');
    expect($bankInfo['account_no'])->toBe('1560024882443');
    expect($bankInfo['account_name'])->toBe('Rizka Firlana');
});

test('invoice getBankInformation returns BRI when payment linked to BRI account', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $invoice = createTestInvoice();
    $customer = $invoice->customer;

    // Retrieve already seeded BRI account
    $briAccount = Account::where('code', '1112')->firstOrFail();

    $payment = Payment::create([
        'payment_number' => 'PAY-TEST-002',
        'invoice_id' => $invoice->id,
        'customer_id' => $customer->id,
        'account_id' => $briAccount->id,
        'amount' => 400000,
        'payment_date' => now(),
        'status' => \App\Enums\PaymentStatus::Verified->value,
    ]);

    $bankInfo = $invoice->getBankInformation();

    expect($bankInfo['bank_name'])->toBe('BRI');
    expect($bankInfo['account_no'])->toBe('093501060489534');
    expect($bankInfo['account_name'])->toBe('Rizka Firlana');
});
