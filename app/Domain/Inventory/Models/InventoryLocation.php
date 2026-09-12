<?php

namespace App\Domain\Inventory\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_code',
        'quantity_on_hand',
        'quantity_reserved',
        'version',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Inventory\Models\Product::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }


    public function availableQuantity(): int
    {
        return $this->quantity_on_hand - $this->quantity_reserved;
    }
}