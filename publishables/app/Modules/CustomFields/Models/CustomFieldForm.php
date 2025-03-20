<?php

namespace App\Modules\CustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldForm extends Model
{
    protected $table = 'custom_fields';
    protected $casts = [
        'deleted_at' => 'datetime'
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function content(): HasMany
    {
        return $this->hasMany(CustomFieldContent::class, 'form_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CustomFieldFormModule::class, 'form_id');
    }
}
