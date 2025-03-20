<?php

namespace App\Models;

use App\Interfaces\CustomDictionnaryInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MetaFramework\Polyglote\Traits\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property string $slug
 */
class Dictionnary extends Model
{

    use HasFactory;
    use Translation;

    public $timestamps = false;
    public array $fillables = [
        'name' =>[
            'type' => 'text',
            'label' => 'Intitulé',
            'required' => true,
        ],
    ];
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->translatable = array_keys($this->fillables);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DictionnaryEntry::class)->whereNull('parent')->whereNull('deleted_at');
    }

    public function entrySubClass(): CustomDictionnaryInterface|bool
    {
        $subclass = "\App\Models\DictionnaryEntry\\".ucfirst(Str::camel($this->slug));

        return class_exists($subclass) ? new $subclass : false;
    }
}
