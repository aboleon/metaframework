<?php

namespace App\Models\Order\Cart;

use App\Interfaces\Stockable;
use App\Models\EventContact;
use App\Models\EventManager\Sellable;
use App\Models\Order;
use App\Models\Vat;
use App\Traits\Orderable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Casts\PriceInteger;

/**
 * @property int           $total_pec
 * @property int           $quantity
 * @property Order         $order
 * @property Sellable|int  $service_id
 * @property Carbon|string $cancellation_request
 * @property int           $cancelled_qty
 */
class ServiceCart extends Model implements Stockable
{
    use HasFactory;
    use Orderable;

    protected $table = 'order_cart_service';

    protected $fillable
        = [
            'order_id',
            'service_id',
            'quantity',
            'unit_price',
            'total_net',
            'total_vat',
            'total_pec',
            'vat_id',
            'event_contact_id',
            'cancellation_request',
            'cancelled_qty',
        ];

    protected $casts
        = [
            'unit_price'           => PriceInteger::class,
            'total_net'            => PriceInteger::class,
            'total_vat'            => PriceInteger::class,
            'total_pec'            => PriceInteger::class,
            'cancellation_request' => 'datetime',
            'cancelled_at'         => 'datetime',
        ];


    public function getStock(): int
    {
        return $this->quantity;
    }

    public function getStockableId(): int
    {
        return $this->service_id;
    }

    public function getStockableType(): string
    {
        return self::class;
    }

    public function getStockableLabel(): string
    {
        return 'Prestations';
    }

    public function service(): BelongsTo
    {
        return self::belongsTo(Sellable::class, 'service_id');
    }

    public function vat(): BelongsTo
    {
        return $this->belongsTo(Vat::class, 'vat_id');
    }

    public function eventContact(): BelongsTo
    {
        return $this->belongsTo(EventContact::class, 'event_contact_id');
    }
}
