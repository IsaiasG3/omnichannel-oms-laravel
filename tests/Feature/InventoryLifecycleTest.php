<?php

use App\Domain\Inventory\Actions\ConsumeInventoryAction;
use App\Domain\Inventory\Actions\ReleaseInventoryAction;
use App\Domain\Inventory\Actions\ReserveInventoryAction;
use App\Domain\Inventory\Models\InventoryLocation;
use App\Domain\Inventory\Models\Product;
use App\Domain\Orders\Models\Order;
use App\Models\User;

it('descuenta el stock físico real al consumir una reserva (envío)', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-TEST-1',
        'user_id' => $user->id,
        'status' => 'pending',
        'total_cents' => 400,
    ]);

    $product = Product::create(['sku' => 'SKU-A', 'name' => 'A', 'price_cents' => 100]);
    $location = InventoryLocation::create([
        'product_id' => $product->id,
        'location_code' => 'L1',
        'quantity_on_hand' => 10,
    ]);

    (new ReserveInventoryAction())->execute($location->id, 4, orderId: $order->id);

    $location->refresh();
    expect($location->quantity_on_hand)->toBe(10) 
        ->and($location->availableQuantity())->toBe(6);

    (new ConsumeInventoryAction())->execute($order->id);

    $location->refresh();
    expect($location->quantity_on_hand)->toBe(6)
        ->and($location->quantity_reserved)->toBe(0)
        ->and($location->availableQuantity())->toBe(6); 
});

it('libera el stock reservado al cancelar una orden', function () {
    $user = User::factory()->create();

    $order = Order::create([
        'order_number' => 'ORD-TEST-2',
        'user_id' => $user->id,
        'status' => 'pending',
        'total_cents' => 400,
    ]);

    $product = Product::create(['sku' => 'SKU-B', 'name' => 'B', 'price_cents' => 100]);
    $location = InventoryLocation::create([
        'product_id' => $product->id,
        'location_code' => 'L2',
        'quantity_on_hand' => 10,
    ]);

    (new ReserveInventoryAction())->execute($location->id, 4, orderId: $order->id);

    $location->refresh();
    expect($location->availableQuantity())->toBe(6);

    (new ReleaseInventoryAction())->execute($order->id);

    $location->refresh();
    expect($location->quantity_on_hand)->toBe(10)
        ->and($location->quantity_reserved)->toBe(0)
        ->and($location->availableQuantity())->toBe(10); 
});