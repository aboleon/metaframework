<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int|null $frontcartline_id
 */
class StockTemp extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'order_temp_stock';

    protected $fillable
        = [
            'uuid',
            'shoppable_type',
            'shoppable_id',
            'quantity',
            'date',
            'room_id',
            'participation_type_id',
            'account_type',
            'account_id',
            'frontcartline_id',
            'on_quota'
        ];

    public function shoppable(): MorphTo
    {
        return $this->morphTo();
    }
}
