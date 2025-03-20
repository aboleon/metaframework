<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;

class AccountPhone extends Model
{
    use HasFactory;

    protected $table = 'account_phones';

    protected $fillable = [
        'name',
        'country_code',
        'phone',
        'default'
    ];

    protected $casts = [
        'phone' => E164PhoneNumberCast::class . ':country_code',
    ];


    protected static function boot()
    {
        parent::boot();

        static::saved(function ($phone) {
            if ($phone->default) {
                static::where('user_id', $phone->user_id)
                    ->where('id', '!=', $phone->id)
                    ->update(['default' => null]);
            }
        });
    }
}
