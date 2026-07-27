<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepositoryInterface<Order>
 */
interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByNumber(string $number): ?Order;

    /** @return LengthAwarePaginator<Order> */
    public function paginateForCustomer(int $customerId, array $filters, int $perPage = 15): LengthAwarePaginator;

    /** @return LengthAwarePaginator<Order> */
    public function paginateForSales(int $salesId, array $filters, int $perPage = 15): LengthAwarePaginator;

    public function generateNumber(): string;
}
