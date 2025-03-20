<?php

namespace App\Models\Order\Cart;

use App\Interfaces\ShoppableInterface;
use App\Interfaces\Stockable;
use App\Models\EventContact;
use App\Models\EventManager\Accommodation;
use App\Models\EventManager\Accommodation\Room;
use App\Models\EventManager\Accommodation\RoomGroup;
use App\Models\Order;
use App\Traits\Orderable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use MetaFramework\Casts\PriceInteger;

/**
 * @property int $room_id
 * @property Order $order
 * @property int $quantity
 * @property int $total_pec
 * @property int $vat_id
 * @property int $unit_price
 * @property int $total_net
 * @property int $total_vat
 * @property int|null $participation_type_id
 * @property string $accompanying_details
 * @property int|null $beneficiary_event_contact_id
 * @property string $comment
 * @property int $room_group_id
 * @property int $event_hotel_id
 * @property string $date
 * @property int $order_id
 * @property Carbon|string $cancellation_request
 * @property int $id
 * @property null|int $amended_cart_id
 * @property int $cancelled_qty
 */
class AccommodationCart extends Model implements ShoppableInterface, Stockable
{
    use HasFactory;
    use Orderable;

    public $timestamps = false;
    protected $table = 'order_cart_accommodation';

    protected $fillable = [
        'order_id',
        'date',
        'room_id',
        'room_group_id',
        'event_hotel_id',
        'quantity',
        'unit_price',
        'total_net',
        'total_vat',
        'total_pec',
        'vat_id',
        'participation_type_id',
        'accompanying_details',
        'comment',
        'event_contact_id',
        'cancellation_request',
        'amended_cart_id',
        'on_quota',
        'cancelled_qty'
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'cancellation_request' => 'datetime',
        'cancelled_at' => 'datetime',
        'unit_price' => PriceInteger::class,
        'total_net' => PriceInteger::class,
        'total_vat' => PriceInteger::class,
        'total_pec' => PriceInteger::class,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }


    public function roomGroup(): BelongsTo
    {
        return $this->belongsTo(RoomGroup::class);
    }

    public function eventHotel(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function shoppable(): MorphTo
    {
        return $this->morphTo();
    }

    public function shoppableVat()
    {
    }

    public function shoppablePrice()
    {
    }

    public function shoppableTitle()
    {
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function getStockableId(): int
    {
        return $this->event_hotel_id;
    }

    public function getStockableType(): string
    {
        return self::class;
    }

    public function getStockableLabel(): string
    {
        return 'Hébergements';
    }

    public function eventContact(): BelongsTo
    {
        return $this->belongsTo(EventContact::class,'event_contact_id');
    }

    public function attributions(): HasMany
    {
        return $this->hasMany(AccommodationAttribution::class, 'cart_id');
    }

    public function wasAmended(): BelongsTo
    {
        return $this->belongsTo(AccommodationCart::class, 'id','amended_cart_id');
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(AccommodationCartCancellation::class, 'cart_id');
    }

    public function computedQuantity(): int
    {
        return $this->quantity - $this->cancelled_qty;
    }

    public function totalCancelledQuantity(): int
    {
        return $this->cancelled_qty;
    }

    public function isFullyCancelled(): bool
    {
        return $this->quantity - $this->cancelled_qty == 0;
    }

}
