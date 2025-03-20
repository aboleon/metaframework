<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $serial
 * @property \Carbon\Carbon $emitted_at
 * @property \Carbon\Carbon $expires_at
 */
class AccountDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'serial',
        'expires_at',
        'emitted_at'
    ];

    protected $casts = [
        'expires_at' => 'date',
        'emitted_at' => 'date'
    ];

}
