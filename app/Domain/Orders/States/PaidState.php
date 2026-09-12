<?php

namespace App\Domain\Orders\States;

class PaidState extends OrderState
{
    public function label(): string
    {
        return 'Pagado';
    }

    public function allowedTransitions(): array
    {
        return [ShippedState::class, CancelledState::class];
    }
}