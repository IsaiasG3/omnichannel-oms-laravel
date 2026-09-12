<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; }
        h1 { font-size: 20px; margin-bottom: 0; }
        .meta { color: #777; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
        th { background: #f5f5f5; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <h1>Factura {{ $invoice->invoice_number }}</h1>
    <p class="meta">
        Orden: {{ $order->order_number }} · Fecha: {{ $invoice->created_at->format('d/m/Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                    <td>${{ number_format($item->subtotal_cents / 100, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td>${{ number_format($order->total_cents / 100, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>