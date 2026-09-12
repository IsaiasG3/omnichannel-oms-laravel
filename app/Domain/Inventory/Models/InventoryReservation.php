<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    protected $fillable = [
        'inventory_location_id',
        'order_id',
        'quantity',
        'status',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id');
    }
}