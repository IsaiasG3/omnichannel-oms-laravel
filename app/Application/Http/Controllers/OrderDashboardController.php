<?php

namespace App\Application\Http\Controllers;

use App\Domain\Orders\Models\Order;
use Illuminate\Support\Facades\Gate; 
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class OrderDashboardController extends Controller
{
public function index(Request $request): Response
{
    $query = Order::with('items')->latest('created_at')->limit(20);

  
    if (! $request->user()->isWarehouseStaff()) {
        $query->where('user_id', $request->user()->id);
    }

    $orders = $query->get()->map(fn (Order $order) => [
        'id' => $order->id,
        'order_number' => $order->order_number,
        'status' => $order->status,
        'status_label' => $order->state()->label(),
        'total_cents' => $order->total_cents,
        'created_at' => $order->created_at->toIso8601String(),
    ]);

    return Inertia::render('Orders/Dashboard', ['orders' => $orders]);
}

    public function show(Order $order): Response
    {
        Gate::authorize('view', $order);
        $order->load('items.product', 'statusHistories');

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->state()->label(),
                'total_cents' => $order->total_cents,
            ],
            'history' => $order->statusHistories->map(fn ($h) => [
                'from_status' => $h->from_status,
                'to_status' => $h->to_status,
                'created_at' => $h->created_at->toIso8601String(),
            ]),
        ]);
    }
}