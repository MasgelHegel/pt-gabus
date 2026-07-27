<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $oldStatus,
        public readonly OrderStatus $newStatus,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new Channel('admin')];

        if ($this->order->customer) {
            $channels[] = new Channel("customer.{$this->order->customer->id}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer?->name,
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
            'new_status_label' => $this->newStatus->label(),
            'total_amount' => (float) $this->order->total_amount,
            'updated_at' => $this->order->updated_at?->toISOString(),
        ];
    }
}
