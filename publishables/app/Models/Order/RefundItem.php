<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFramework\Casts\Datepicker;
use MetaFramework\Casts\PriceInteger;

/**
 * @property string $batch_id
 */
class RefundItem extends Model
{

    use HasFactory;
    public $timestamps = false;
    protected $table = 'order_refunds_items';

    protected $fillable = [
        'date',
        'amount',
        'object',
        'vat_id'
    ];

    protected $casts = [
        'date' => Datepicker::class,
        'amount' => PriceInteger::class
    ];


}
