<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** @extends BaseRepository<Order> */
class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findByNumber(string $number): ?Order
    {
        /** @var Order|null */
        return $this->model->newQuery()->where('order_number', $number)->first();
    }

    /** @return LengthAwarePaginator<Order> */
    public function paginateForCustomer(int $customerId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['items.product', 'salesOrder'])
            ->where('customer_id', $customerId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /** @return LengthAwarePaginator<Order> */
    public function paginateForSales(int $salesId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['customer', 'items.product'])
            ->where('sales_id', $salesId);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function generateNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';
        $last   = $this->model->newQuery()
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
