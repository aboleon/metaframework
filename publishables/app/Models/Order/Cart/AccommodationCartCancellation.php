<?php

namespace App\Models\Order\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationCartCancellation extends Model
{
    public $timestamps = false;
    protected $table = 'order_cart_accommodation_cancellations';

    protected $fillable
        = [
            'quantity',
            'cancelled_at',
        ];

    protected $casts
        = [
            'requested_at' => 'datetime:Y-m-d H:i:s',
            'cancelled_at' => 'datetime:Y-m-d H:i:s',
        ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(AccommodationCart::class, 'cart_id', 'id');
    }
}
