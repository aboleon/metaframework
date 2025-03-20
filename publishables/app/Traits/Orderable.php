<?php

namespace App\Traits;

use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Orderable
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
