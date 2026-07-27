<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Enums\PaymentStatus;
use App\Models\Payment;

/** @extends BaseRepository<Payment> */
class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function generateNumber(): string
    {
        $prefix = 'PAY-' . now()->format('Ym') . '-';
        $last   = $this->model->newQuery()
            ->where('payment_number', 'like', $prefix . '%')
            ->orderByDesc('payment_number')
            ->value('payment_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function findPendingByInvoice(int $invoiceId): ?Payment
    {
        /** @var Payment|null */
        return $this->model->newQuery()
            ->where('invoice_id', $invoiceId)
            ->where('status', PaymentStatus::Pending->value)
            ->latest()
            ->first();
    }
}
