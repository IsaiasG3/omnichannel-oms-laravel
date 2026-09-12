<?php

namespace App\Domain\Orders\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}