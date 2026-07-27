<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Shipment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShipmentConfirmed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Shipment $shipment,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'shipment.confirmed';
    }

    public function broadcastWith(): array
    {
        return [
            'shipment_number' => $this->shipment->shipment_number,
            'sales_order_number' => $this->shipment->salesOrder?->so_number,
            'customer_name' => $this->shipment->salesOrder?->customer?->name,
            'status' => $this->shipment->status,
            'confirmed_at' => $this->shipment->customer_confirmed_at?->toISOString(),
        ];
    }
}
