<?php

namespace App\Models\Order\Cart;

use App\Models\EventManager\Accommodation;
use App\Models\EventManager\Accommodation\Room;
use App\Models\EventManager\Traits\AccommodationTrait;
use App\Traits\Orderable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Casts\PriceInteger;

/**
 * @property int $order_id
 * @property int $event_hotel_id
 * @property int $room_id
 * @property int $amount
 * @property int $amount_net
 * @property int $amount_vat
 * @property int $vat_id
 * @property int|null $beneficiary_event_contact_id
 * @property int $amount_pec
 */
class TaxRoomCart extends Model
{
    use HasFactory;
    use AccommodationTrait;
    use Orderable;

    protected $table = 'order_cart_taxroom';
    protected $fillable = [
        'order_id',
        'quantity',
        'event_hotel_id',
        'room_id',
        'amount',
        'amount_net',
        'amount_vat',
        'amount_pec',
        'vat_id'
    ];

    protected $casts = [
        'amount' => PriceInteger::class,
        'amount_net' => PriceInteger::class,
        'amount_vat' => PriceInteger::class,
        'amount_pec' => PriceInteger::class,
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function eventHotel(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function title(): string
    {
        return __('cart.accommodation_application_fee');
    }
}
