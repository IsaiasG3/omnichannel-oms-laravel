<?php

namespace App\Domain\Orders\States;

class PendingState extends OrderState
{
    public function label(): string
    {
        return 'Pendiente de pago';
    }

    public function allowedTransitions(): array
    {
        return [PaidState::class, CancelledState::class];
    }
}