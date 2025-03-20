<?php

namespace App\Models\Order\Cart;

use App\Models\EventManager\Accommodation\Room;
use App\Models\EventManager\Sellable;
use App\Models\Order\Attribution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationAttribution extends Attribution
{
    use HasFactory;

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'shoppable_id');
    }

    public function shoppable(): BelongsTo
    {
        return $this->room();
    }
}
