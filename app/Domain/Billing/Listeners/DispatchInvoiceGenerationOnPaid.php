<?php

namespace App\Domain\Billing\Listeners;

use App\Domain\Billing\Jobs\GenerateInvoiceJob;
use App\Domain\Orders\Events\OrderStatusChanged;

class DispatchInvoiceGenerationOnPaid
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->toStatus === 'paid') {
            GenerateInvoiceJob::dispatch($event->order->id)
                ->onQueue('invoices');
        }
    }
}