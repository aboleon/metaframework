<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use MetaFramework\Casts\PriceInteger;


/**
 * @property FrontCart        $cart
 * @property FrontTransaction $transaction
 * @property int              $order_id
 */
class PaymentCall extends Model
{

    public $timestamps = false;
    protected $table = "front_payment_calls";
    protected $fillable
        = [
            'provider',
            'cart_id',
            'group_manager_id',
            'order_id',
            'total',
            'closed_at',
        ];
    protected $casts
        = [
            'total'     => PriceInteger::class,
            'closed_at' => 'datetime',
        ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(FrontCart::class, 'cart_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(FrontTransaction::class, 'payment_call_id');
    }

    public function isGroupManager(): bool
    {
        return ! is_null($this->group_manager_id);
    }


}
