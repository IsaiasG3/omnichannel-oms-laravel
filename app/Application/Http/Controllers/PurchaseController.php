<?php

namespace App\Application\Http\Controllers;

use App\Domain\Inventory\Actions\ReserveInventoryAction;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PurchaseController extends Controller
{
    public function attempt(Request $request, ReserveInventoryAction $action): JsonResponse
    {
        $validated = $request->validate([
            'inventory_location_id' => ['required', 'integer', 'exists:inventory_locations,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $reservation = $action->execute(
                $validated['inventory_location_id'],
                $validated['quantity']
            );

            return response()->json([
                'success' => true,
                'reservation_id' => $reservation->id,
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409); 
        }
    }
}