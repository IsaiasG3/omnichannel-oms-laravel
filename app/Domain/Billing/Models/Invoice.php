<?php

namespace App\Domain\Billing\Models;

use App\Domain\Orders\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'order_id',
        'invoice_number',
        'pdf_path',
        'status', 
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Domain\Orders\Models\Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}