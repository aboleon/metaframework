<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accompanying extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'order_accompanying';

    protected $fillable = [
        'order_id',
        'room_id',
        'total',
        'names',
    ];
}
