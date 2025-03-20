<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Interfaces\GooglePlacesInterface;

/**
 * @property string $country_code;
 * @property string $locality;/**
 * @mixin Model
 * /
 */
class AccountAddress extends Model implements GooglePlacesInterface
{
    use HasFactory;

    protected $fillable = [
        'company',
        'postal_code',
        'country_code',
        'street_number',
        'locality',
        'cedex',
        'route',
        'lat',
        'lon',
        'name',
        'complementary',
        'billing',
        'text_address'
    ];

    protected $table = 'account_address';


    protected static function boot()
    {
        parent::boot();

        static::saved(function ($address) {
            if ($address->billing == 1 && $address->account) {
                $address->account->address()->where('id', '!=', $address->id)->update(['billing' => null]);
            }
        });


        static::deleted(function ($address) {
            if ($address->billing == 1) {
                $newBillingAddress = $address->account->address()
                    ->whereNotNull('company')
                    ->orderBy('id')
                    ->first();
                if (!$newBillingAddress) {
                    $newBillingAddress = $address->account->address()
                        ->orderBy('id')
                        ->first();
                }
                if ($newBillingAddress) {
                    $newBillingAddress->billing = 1;
                    $newBillingAddress->save();
                }
            }
        });

    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'user_id');
    }
}
