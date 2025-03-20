<?php

namespace App\Models;

use App\Casts\StringableArray;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MetaFramework\Casts\PriceInteger;
use MetaFramework\Mediaclass\Interfaces\MediaclassInterface;
use MetaFramework\Mediaclass\Traits\Mediaclass;
use MetaFramework\Polyglote\Traits\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Hotel extends Model implements MediaclassInterface
{
    use HasFactory;
    use Translation;
    use Mediaclass;

    protected $fillable = [
        'name',
        'stars',
        'description',
        'first_name',
        'last_name',
        'email',
        'phone',
        'services',
        'website'
    ];

    protected $casts = [
        'services' => StringableArray::class,
        'comission' => PriceInteger::class
    ];

    public array $fillables = [
        'description' => [
            'type' => 'textarea',
            'label' => 'Description',
        ],
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->defineTranslatables();
    }

    public function address(): HasOne
    {
        return $this->hasOne(HotelAddress::class);
    }

    public function mediaSettings(): array
    {
        return [
            '_media' =>
                [
                    'label' => 'Photo',
                ]
        ];
    }
}
