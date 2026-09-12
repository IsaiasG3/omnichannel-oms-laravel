<?php

namespace App\Domain\Orders\Models;

use App\Domain\Orders\States\OrderState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'channel',
        'status',
        'total_cents',
        'currency',
        'paid_at',
        'shipped_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('created_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function state(): OrderState
    {
        return OrderState::fromKey($this->status, $this);
    }
}