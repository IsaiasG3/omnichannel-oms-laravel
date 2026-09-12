<?php

namespace App\Application\Http\Controllers;

use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Product;
use App\Domain\Orders\Actions\CreateOrderAction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function create(): Response
    {
        $products = Product::with('inventoryLocations')
            ->where('is_active', true)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price_cents' => $p->price_cents,
                'available' => $p->inventoryLocations->sum(fn ($loc) => $loc->availableQuantity()),
            ]);

        return Inertia::render('Checkout/Create', ['products' => $products]);
    }

    public function store(Request $request, CreateOrderAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $order = $action->execute($request->user(), $validated['items']);
        } catch (InsufficientStockException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Orden creada correctamente.');
    }
}