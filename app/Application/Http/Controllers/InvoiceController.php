<?php

namespace App\Application\Http\Controllers;

use App\Domain\Billing\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $invoices = Invoice::with('order')
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'order_number' => $invoice->order->order_number,
                'order_id' => $invoice->order_id,
                'created_at' => $invoice->created_at->toIso8601String(),
            ]);

        return Inertia::render('Invoices/Index', ['invoices' => $invoices]);
    }

      public function download(Invoice $invoice)
    {
        Gate::authorize('view', $invoice->order); 

        return Storage::disk('public')->response(
            $invoice->pdf_path,
            "{$invoice->invoice_number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}