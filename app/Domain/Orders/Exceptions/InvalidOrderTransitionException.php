<?php

namespace App\Domain\Orders\Exceptions;

use Exception;

class InvalidOrderTransitionException extends Exception
{
    public static function make(string $from, string $to): self
    {
        return new self("No se puede transicionar la orden de '{$from}' a '{$to}'.");
    }
}