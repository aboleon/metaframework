<?php

namespace App\Models\Order;

use App\Enum\OrderClientType;
use App\Models\Account;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Interfaces\GooglePlacesInterface;

class Invoiceable extends Model implements GooglePlacesInterface
{
    use HasFactory;
    protected $table = 'order_invoiceable';

    protected $fillable = [
        'account_id',
        'account_type',
        'address_id',
        'company',
        'vat_number',
        'first_name',
        'last_name',
        'postal_code',
        'country_code',
        'street_number',
        'locality',
        'cedex',
        'route',
        'department',
        'complementary',
        'text_address'
    ];

    public function account(): BelongsTo
    {
        return match ($this->account_type) {
            OrderClientType::GROUP->value => $this->belongsTo(Group::class, 'account_id'),
            default => $this->belongsTo(Account::class, 'account_id'),
        };
    }
}


