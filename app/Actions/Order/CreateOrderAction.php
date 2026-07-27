<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderStatus;
use App\Events\OrderSubmitted;
use App\Models\Order;
use App\Models\OrderItem;

class CreateOrderAction
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
    ) {}

    /**
     * @param array<int, array{product_id: int, quantity: int, unit_price: float}> $items
     */
    public function __invoke(int $customerId, array $items, ?string $notes = null): Order
    {
        $totalAmount = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

        /** @var Order $order */
        $order = $this->repository->create([
            'order_number' => $this->repository->generateNumber(),
            'customer_id'  => $customerId,
            'status'       => OrderStatus::Submitted,
            'total_amount' => $totalAmount,
            'notes'        => $notes,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $order->load('items.product', 'customer');

        OrderSubmitted::dispatch($order);

        return $order;
    }
}
