<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Payment;

/**
 * @extends BaseRepositoryInterface<Payment>
 */
interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function generateNumber(): string;
    public function findPendingByInvoice(int $invoiceId): ?Payment;
}
