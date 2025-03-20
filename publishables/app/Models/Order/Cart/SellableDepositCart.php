<?php

namespace App\Models\Order\Cart;

use App\Models\EventContact;
use App\Models\Order\EventDeposit;
use App\Models\Vat;
use App\Traits\Orderable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Casts\PriceInteger;

class SellableDepositCart extends Model
{
    use HasFactory;
    use Orderable;
    protected $table = 'order_cart_sellable_deposit';

    protected $guarded = [];

    protected $casts = [
        'unit_price' => PriceInteger::class,
        'total_net' => PriceInteger::class,
        'total_vat' => PriceInteger::class,
    ];


    public function deposit(): BelongsTo
    {
        return $this->belongsTo(EventDeposit::class, 'event_deposit_id');
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
