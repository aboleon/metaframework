<?php

namespace App\Models;

use MetaFramework\Actions\Translator;
use MetaFramework\Casts\PriceInteger;
use MetaFramework\Mediaclass\Interfaces\MediaclassInterface;
use MetaFramework\Mediaclass\Traits\Mediaclass;
use MetaFramework\Polyglote\Traits\Translation;
use MetaFramework\Traits\AccessKey;
use MetaFramework\Traits\OnlineStatus;
use App\Traits\Price;
use Illuminate\Database\Eloquent\{Collection as EloquentCollection,
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\MorphTo,
    SoftDeletes};
use Throwable;

class Sellable extends Model implements MediaclassInterface
{
    use HasFactory;

    use AccessKey;
    use Mediaclass;
    use OnlineStatus;
    use Price;
    use SoftDeletes;
    use Translation;


    protected $guarded = [];
    protected $table = 'sellables';

    protected $casts = [
      'price' => PriceInteger::class,
      'price_buy' => PriceInteger::class,
    ];

    protected ?Client $client = null;

    public array $fillables = [
        'title' => [
            'type' => 'text',
            'label' => 'Intitulé',
            'required' => true,
        ],
        'description' => [
            'type' => 'textarea',
            'label' => 'Description',
            'required' => true,
        ],
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->defineTranslatables();
    }

    public function vat(): BelongsTo
    {
        return $this->belongsTo(Vat::class, 'vat_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DictionnaryEntry::class,'category_id');
    }


}
