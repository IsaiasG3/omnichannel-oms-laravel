<?php

namespace App\Domain\Orders\Events;

use App\Domain\Orders\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public ?string $fromStatus,
        public string $toStatus,
    ) {}

 
    public function broadcastOn(): array
    {
        return [new Channel('orders-dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->toStatus,
                'status_label' => $this->order->state()->label(),
                'total_cents' => $this->order->total_cents,
                'updated_at' => $this->order->updated_at?->toIso8601String(),
            ],
            'from_status' => $this->fromStatus,
        ];
    }
}