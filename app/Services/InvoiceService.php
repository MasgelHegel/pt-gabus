<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends BaseService<Invoice> */
class InvoiceService extends BaseService
{
    public function __construct(
        InvoiceRepositoryInterface $repository,
    ) {
        parent::__construct($repository);
    }

    public function findById(int $id): Invoice
    {
        /** @var Invoice */
        return $this->repository->findById($id);
    }

    /** @return LengthAwarePaginator<Invoice> */
    public function paginateForCustomer(int $customerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateForCustomer($customerId, $filters, $perPage);
    }
}
