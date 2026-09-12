<?php

namespace App\Domain\Orders\States;

use App\Domain\Orders\Models\Order;

abstract class OrderState
{
    public function __construct(protected Order $order) {}

    abstract public function label(): string;

    /**
     * Lista de estados (clases) a los que se permite transicionar desde este.
     *
     * @return array<class-string<OrderState>>
     */
    abstract public function allowedTransitions(): array;

    public function canTransitionTo(string $stateClass): bool
    {
        return in_array($stateClass, $this->allowedTransitions(), true);
    }

    public static function key(): string
    {
        return match (static::class) {
            PendingState::class => 'pending',
            PaidState::class => 'paid',
            ShippedState::class => 'shipped',
            DeliveredState::class => 'delivered',
            CancelledState::class => 'cancelled',
            default => throw new \LogicException('Estado sin key mapeado.'),
        };
    }

    public static function fromKey(string $key, Order $order): self
    {
        return match ($key) {
            'pending' => new PendingState($order),
            'paid' => new PaidState($order),
            'shipped' => new ShippedState($order),
            'delivered' => new DeliveredState($order),
            'cancelled' => new CancelledState($order),
            default => throw new \InvalidArgumentException("Estado desconocido: {$key}"),
        };
    }
}