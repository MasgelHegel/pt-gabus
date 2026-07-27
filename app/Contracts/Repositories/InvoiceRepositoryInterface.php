<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Invoice>
 */
interface InvoiceRepositoryInterface extends BaseRepositoryInterface
{
    /** @return LengthAwarePaginator<Invoice> */
    public function paginateForCustomer(int $customerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    /** @return Collection<int, Invoice> */
    public function getOverdueInvoices(): Collection;

    public function generateNumber(): string;
}
