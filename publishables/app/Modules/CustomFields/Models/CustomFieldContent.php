<?php

namespace App\Modules\CustomFields\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CustomFieldContent extends Model
{
    public $timestamps = false;
    protected $table = 'custom_fields_content';

    protected $fillable = [
        'key',
        'value'
    ];


    public function form(): BelongsTo
    {
        return $this->belongsTo(CustomFieldForm::class, 'form_id');
    }


}
