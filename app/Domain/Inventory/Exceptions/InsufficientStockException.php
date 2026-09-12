<?php

namespace App\Domain\Inventory\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public static function forLocation(int $locationId, int $requested, int $available): self
    {
        return new self(
            "Stock insuficiente en la ubicación #{$locationId}. Solicitado: {$requested}, disponible: {$available}."
        );
    }
}