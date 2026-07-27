<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;

class VerifyPaymentAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepo,
    ) {}

    public function __invoke(int $paymentId, int $accountId): bool
    {
        /** @var Payment $payment */
        $payment = Payment::with(['invoice'])->findOrFail($paymentId);

        $payment->update([
            'status'      => PaymentStatus::Verified,
            'account_id'  => $accountId,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        // Update invoice to paid
        $payment->invoice->update(['status' => InvoiceStatus::Paid]);

        // Kurangi piutang customer
        $this->customerRepo->decrementPiutang($payment->customer_id, (float) $payment->amount);

        // Fire event untuk journal otomatis → akan ditangani observer
        // event(new PaymentVerified($payment));

        return true;
    }
}
