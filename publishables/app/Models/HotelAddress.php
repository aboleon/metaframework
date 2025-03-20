<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MetaFramework\Interfaces\GooglePlacesInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelAddress extends Model implements GooglePlacesInterface
{
    use HasFactory;
    protected $table = 'hotel_address';
    protected $fillable = [
        'street_number',
        'route',
        'postal_code',
        'locality',
        'country_code',
        'administrative_area_level_1',
        'administrative_area_level_2',
        'text_address',
        'lat',
        'lon',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
