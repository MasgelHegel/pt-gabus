<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer?->name,
            'total_amount' => (float) $this->order->total_amount,
            'status' => $this->order->status->value,
            'created_at' => $this->order->created_at?->toISOString(),
        ];
    }
}
