<?php

namespace App\Application\Http\Controllers;

use App\Domain\Inventory\Models\InventoryLocation;
use App\Domain\Orders\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $counts = Order::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $lowStock = InventoryLocation::with('product')
            ->get()
            ->filter(fn (InventoryLocation $loc) => $loc->availableQuantity() <= 5)
            ->map(fn (InventoryLocation $loc) => [
                'product_name' => $loc->product->name,
                'available' => $loc->availableQuantity(),
            ])
            ->values();

        $recentOrders = Order::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status_label' => $o->state()->label(),
                'total_cents' => $o->total_cents,
            ]);

        return Inertia::render('Dashboard', [
            'counts' => [
                'pending' => (int) $counts->get('pending', 0),
                'paid' => (int) $counts->get('paid', 0),
                'shipped' => (int) $counts->get('shipped', 0),
                'delivered' => (int) $counts->get('delivered', 0),
                'cancelled' => (int) $counts->get('cancelled', 0),
            ],
            'lowStock' => $lowStock,
            'recentOrders' => $recentOrders,
        ]);
    }
}