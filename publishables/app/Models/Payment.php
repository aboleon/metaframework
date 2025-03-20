<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Casts\PriceInteger;

/**
 * @property int|mixed $order_id
 */
class Payment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "order_payments";
    protected $fillable = [
        'order_id',
        'transaction_id',
        'transaction_origin',
        'date',
        'amount',
        'payment_method',
        'authorization_number',
        'card_number',
        'bank',
        'issuer',
        'check_number'
    ];
    protected $casts = [
        "date" => "date",
        "amount" => PriceInteger::class,
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

}
