<?php

namespace App\Domain\Orders\States;

class DeliveredState extends OrderState
{
    public function label(): string
    {
        return 'Entregado';
    }

    public function allowedTransitions(): array
    {
        return []; 
    }
}