<?php

namespace App\Domain\Orders\States;

class ShippedState extends OrderState
{
    public function label(): string
    {
        return 'Enviado';
    }

    public function allowedTransitions(): array
    {
        return [DeliveredState::class];
    }
}