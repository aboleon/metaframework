<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Polyglote\Traits\Translation;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    use Translation;

    public $timestamps = false;

    public array $fillables = [
        'name' =>[
            'type' => 'text',
            'label' => 'Intitulé',
        ],
    ];
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->translatable = array_keys($this->fillables);
    }


    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }
}
