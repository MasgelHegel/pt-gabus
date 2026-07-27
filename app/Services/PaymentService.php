<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Payment\UploadPaymentProofAction;
use App\Actions\Payment\VerifyPaymentAction;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/** @extends BaseService<Payment> */
class PaymentService extends BaseService
{
    public function __construct(
        PaymentRepositoryInterface $repository,
        private readonly UploadPaymentProofAction $uploadAction,
        private readonly VerifyPaymentAction $verifyAction,
    ) {
        parent::__construct($repository);
    }

    public function uploadProof(int $invoiceId, int $customerId, UploadedFile $file, float $amount, string $date): Payment
    {
        return DB::transaction(fn () => ($this->uploadAction)($invoiceId, $customerId, $file, $amount, $date));
    }

    public function verify(int $paymentId, int $accountId): bool
    {
        return DB::transaction(fn () => ($this->verifyAction)($paymentId, $accountId));
    }

    public function reject(int $paymentId, string $reason): bool
    {
        /** @var Payment $payment */
        $payment = $this->repository->findById($paymentId);
        $payment->update([
            'status'           => \App\Enums\PaymentStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        return true;
    }
}
