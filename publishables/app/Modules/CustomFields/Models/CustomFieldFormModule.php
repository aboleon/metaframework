<?php

namespace App\Modules\CustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomFieldFormModule extends Model
{
    use SoftDeletes;

    protected $table = 'custom_fields_modules';
    protected $fillable = [
        'title',
        'required',
        'type',
        'subtype',
        'key',
        'position'
    ];

    public function data(): HasMany
    {
        return $this->hasMany(CustomFieldFormModuleData::class, 'module_id');
    }
}
