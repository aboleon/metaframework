<?php

namespace App\Models\Order;

use App\Models\EventContact;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attribution extends Model
{
    use HasFactory;

    protected $table = 'order_attributions';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'event_contact_id',
        'shoppable_type',
        'shoppable_id',
        'quantity',
        'assigned_by',
        'attributes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'attributes' => 'array',
    ];

    public function eventContact(): BelongsTo
    {
        return $this->belongsTo(EventContact::class, 'event_contact_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
/*
    public function shoppable(): BelongsTo
    {
        return match($this->shoppable_type) {
            OrderCartType::ACCOMMODATION->value => $this->belongsTo(Sellable::class, 'shoppable_id'),
            default => $this->belongsTo(Sellable::class, 'shoppable_id')
        };
    }
    */
}
