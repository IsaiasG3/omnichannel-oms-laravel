<?php

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\Models\InventoryLocation;
use App\Domain\Inventory\Models\InventoryReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsumeInventoryAction
{
 
    public function execute(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $reservations = InventoryReservation::where('order_id', $orderId)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                /** @var InventoryLocation $location */
                $location = InventoryLocation::query()
                    ->lockForUpdate()
                    ->findOrFail($reservation->inventory_location_id);

                $location->quantity_on_hand = max(0, $location->quantity_on_hand - $reservation->quantity);
                $location->quantity_reserved = max(0, $location->quantity_reserved - $reservation->quantity);
                $location->save();

                $reservation->update(['status' => 'consumed']);

                Log::info('Inventory consumed (shipped)', [
                    'reservation_id' => $reservation->id,
                    'location_id' => $location->id,
                    'quantity' => $reservation->quantity,
                ]);
            }
        });
    }
}