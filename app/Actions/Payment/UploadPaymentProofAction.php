<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Events\PaymentUploaded;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadPaymentProofAction
{
    public function __construct(
        private readonly PaymentRepositoryInterface $repository,
    ) {}

    public function __invoke(int $invoiceId, int $customerId, UploadedFile $file, float $amount, string $date): Payment
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $path = $file->store('payment-proofs', 'public');

        /** @var Payment $payment */
        $payment = $this->repository->create([
            'payment_number' => $this->repository->generateNumber(),
            'invoice_id'     => $invoiceId,
            'customer_id'    => $customerId,
            'amount'         => $amount,
            'payment_date'   => $date,
            'proof_file'     => $path,
            'status'         => PaymentStatus::Pending,
        ]);

        // Update invoice status
        $invoice->update(['status' => InvoiceStatus::PaymentUploaded]);

        $payment = $payment->fresh(['invoice', 'customer']);

        PaymentUploaded::dispatch($payment);

        return $payment;
    }
}
