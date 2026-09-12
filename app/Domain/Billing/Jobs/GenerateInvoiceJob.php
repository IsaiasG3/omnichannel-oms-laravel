<?php

namespace App\Domain\Billing\Jobs;

use App\Domain\Billing\Models\Invoice;
use App\Domain\Orders\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::with('items.product')->findOrFail($this->orderId);

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => 'INV-' . Str::upper(Str::random(8)),
            'status' => 'generating',
        ]);

       Log::info("Generando factura para la orden #{$order->order_number}...");

        $pdf = Pdf::loadView('invoices.pdf', [
            'order' => $order,
            'invoice' => $invoice,
        ]);

        $pdfContent = $pdf->output();
        
        if (empty($pdfContent)) {
            throw new \Exception("El contenido del PDF está vacío. Posible error en la vista HTML.");
        }

        $pdfPath = "invoices/{$invoice->invoice_number}.pdf";
        $guardado = Storage::disk('local')->put($pdfPath, $pdfContent);

        if (!$guardado) {
            throw new \Exception("Fallo al guardar el PDF en el disco. Permisos o ruta inválida.");
        }

        $invoice->update([
            'pdf_path' => $pdfPath,
            'status' => 'generated',
        ]);

        Log::info("Factura {$invoice->invoice_number} generada correctamente.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Fallo generando factura para orden #{$this->orderId}: {$exception->getMessage()}");
    }
}