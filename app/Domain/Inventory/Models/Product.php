<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price_cents',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function inventoryLocations(): HasMany
    {
        return $this->hasMany(InventoryLocation::class);
    }

    public function priceInPesos(): float
    {
        return $this->price_cents / 100;
    }
}