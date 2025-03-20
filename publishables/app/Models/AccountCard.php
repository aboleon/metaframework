<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $serial
 * @property \Carbon\Carbon $expires_at
 * @property string $name
 */
class AccountCard extends Model
{

    use HasFactory;
    protected $fillable = [
        'name',
        'serial',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date'
    ];
}
