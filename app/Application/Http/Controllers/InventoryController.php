<?php

namespace App\Application\Http\Controllers;

use App\Domain\Inventory\Models\InventoryLocation;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(): Response
    {
        $locations = InventoryLocation::with('product')
            ->get()
            ->map(fn (InventoryLocation $loc) => [
                'id' => $loc->id,
                'location_code' => $loc->location_code,
                'product_name' => $loc->product->name,
                'available' => $loc->availableQuantity(),
            ]);

        return Inertia::render('Inventory/Index', [
            'locations' => $locations,
        ]);
    }
}