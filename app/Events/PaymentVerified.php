<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentVerified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Payment $payment,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new Channel('admin')];

        if ($this->payment->customer) {
            $channels[] = new Channel("customer.{$this->payment->customer->id}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'payment.verified';
    }

    public function broadcastWith(): array
    {
        return [
            'payment_number' => $this->payment->payment_number,
            'invoice_number' => $this->payment->invoice?->invoice_number,
            'customer_name' => $this->payment->customer?->name,
            'amount' => (float) $this->payment->amount,
            'status' => $this->payment->status->value,
            'verified_at' => $this->payment->verified_at?->toISOString(),
        ];
    }
}
