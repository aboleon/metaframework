<?php

namespace App\Models;

use App\Enum\OrderOrigin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $transaction_id
 * @property string $transaction_call_id
 * @property array  $details
 */
class PaymentTransaction extends Model
{

    public $timestamps = false;
    protected $table = 'payment_transaction';
    protected $fillable
        = [
            'transaction_id',
            'transaction_call_id',
            'return_code',
            'details',
        ];

    protected $casts
        = [
            'details' => 'array',
        ];

    public function paymentCall(): BelongsTo
    {
        return $this->belongsTo(CustomPaymentCall::class, 'payment_call_id');
    }

    public function storedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'transaction_id')->where('transaction_origin', OrderOrigin::BACK->value);
    }
}
