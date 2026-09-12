<?php

namespace App\Domain\Orders\States;

class CancelledState extends OrderState
{
    public function label(): string
    {
        return 'Cancelado';
    }

    public function allowedTransitions(): array
    {
        return [];
    }
}