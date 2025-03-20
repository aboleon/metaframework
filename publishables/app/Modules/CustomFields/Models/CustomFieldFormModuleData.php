<?php

namespace App\Modules\CustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomFieldFormModuleData extends Model
{
    protected $table = 'custom_fields_modules_data';
    protected $fillable = [
        'key',
        'type',
        'content'
    ];
    public $timestamps = false;

    public function module(): BelongsTo
    {
        return $this->belongsTo(CustomFieldFormModule::class, 'module_id');
    }
}
