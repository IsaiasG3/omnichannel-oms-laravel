<?php

namespace App\Domain\Orders\Actions;

use App\Domain\Inventory\Actions\ReserveInventoryAction;
use App\Domain\Inventory\Models\InventoryLocation;
use App\Domain\Inventory\Models\Product;
use App\Domain\Orders\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function __construct(
        private ReserveInventoryAction $reserveInventoryAction,
    ) {}

    /**
     * @param array<int, array{product_id:int, quantity:int}> $items
     */
    public function execute(User $user, array $items, string $channel = 'web'): Order
    {
        return DB::transaction(function () use ($user, $items, $channel) {

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'channel' => $channel,
                'status' => 'pending',
                'total_cents' => 0,
            ]);

            $totalCents = 0;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = (int) $item['quantity'];

                
                $location = InventoryLocation::where('product_id', $product->id)
                    ->orderByDesc('quantity_on_hand')
                    ->firstOrFail();

               
                $this->reserveInventoryAction->execute($location->id, $quantity, $order->id);

                $subtotal = $product->price_cents * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'inventory_location_id' => $location->id,
                    'quantity' => $quantity,
                    'unit_price_cents' => $product->price_cents,
                    'subtotal_cents' => $subtotal,
                ]);

                $totalCents += $subtotal;
            }

            $order->update(['total_cents' => $totalCents]);

            return $order->fresh('items.product');
        });
    }
}