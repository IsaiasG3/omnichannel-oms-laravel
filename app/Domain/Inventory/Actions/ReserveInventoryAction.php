<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\InventoryLocation;
use App\Domain\Inventory\Models\InventoryReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReserveInventoryAction
{

    public function execute(int $inventoryLocationId, int $quantity, ?int $orderId = null): InventoryReservation
    {
        return DB::transaction(function () use ($inventoryLocationId, $quantity, $orderId) {

            /** @var InventoryLocation $location */
            $location = InventoryLocation::query()
                ->lockForUpdate()
                ->findOrFail($inventoryLocationId);

            $available = $location->availableQuantity();

            if ($available < $quantity) {
                throw InsufficientStockException::forLocation(
                    $inventoryLocationId,
                    $quantity,
                    $available
                );
            }

         
            $location->quantity_reserved += $quantity;
            $location->version += 1;
            $location->save();

            $reservation = InventoryReservation::create([
                'inventory_location_id' => $location->id,
                'order_id' => $orderId,
                'quantity' => $quantity,
                'status' => 'active',
            ]);

            Log::info('Inventory reserved', [
                'location_id' => $location->id,
                'quantity' => $quantity,
                'remaining_available' => $location->availableQuantity(),
            ]);

            return $reservation;
        }, attempts: 3); 
    }
}