<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Order\CreateOrderAction;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** @extends BaseService<Order> */
class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface $repository,
        private readonly CreateOrderAction $createAction,
    ) {
        parent::__construct($repository);
    }

    public function createCustomerOrder(int $customerId, array $items, ?string $notes = null): Order
    {
        return DB::transaction(fn () => ($this->createAction)($customerId, $items, $notes));
    }

    public function findById(int $id): Order
    {
        /** @var Order */
        return $this->repository->findById($id);
    }

    /** @return LengthAwarePaginator<Order> */
    public function paginateForCustomer(int $customerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateForCustomer($customerId, $filters, $perPage);
    }
}
