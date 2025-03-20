<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomNote extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'order_room_notes';

    protected $fillable = [
        'order_id',
        'room_id',
        'note',
        'user_id',
    ];
}
