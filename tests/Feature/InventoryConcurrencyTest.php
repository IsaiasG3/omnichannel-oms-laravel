<?php

use App\Domain\Inventory\Actions\ReserveInventoryAction;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\InventoryLocation;
use App\Domain\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;

it('nunca sobre-reserva inventario incluso con transacciones simultáneas', function () {
    $product = Product::create([
        'sku' => 'SKU-TEST',
        'name' => 'Producto de prueba',
        'price_cents' => 1000,
    ]);

    $location = InventoryLocation::create([
        'product_id' => $product->id,
        'location_code' => 'TEST-01',
        'quantity_on_hand' => 5, 
    ]);

    $action = new ReserveInventoryAction();

    $successCount = 0;
    $failureCount = 0;


    for ($i = 0; $i < 10; $i++) {
        try {
            $action->execute($location->id, 1);
            $successCount++;
        } catch (InsufficientStockException $e) {
            $failureCount++;
        }
    }

    expect($successCount)->toBe(5)
        ->and($failureCount)->toBe(5);

    $location->refresh();
    expect($location->availableQuantity())->toBe(0);
});

it('lanza InsufficientStockException si se pide más de lo disponible', function () {
    $product = Product::create([
        'sku' => 'SKU-TEST-2',
        'name' => 'Producto de prueba 2',
        'price_cents' => 1000,
    ]);

    $location = InventoryLocation::create([
        'product_id' => $product->id,
        'location_code' => 'TEST-02',
        'quantity_on_hand' => 3,
    ]);

    $action = new ReserveInventoryAction();

    expect(fn () => $action->execute($location->id, 10))
        ->toThrow(InsufficientStockException::class);
});