<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forms extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'meta_forms';

    public static function selectables(): array
    {
        $forms = [];
        $data = collect(config('forms'))->pluck('name')->toArray();

        foreach($data as $item) {
            $forms[$item] = __('forms.labels.'.$item);
        }
        return $forms;

    }
}
