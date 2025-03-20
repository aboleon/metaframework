<?php

namespace App\Models\Order;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property string $batch_id
 */
class Refund extends Model
{
    use HasFactory;
    protected $table = 'order_refunds';

    protected $fillable = [
        'refund_number',
        'created_by',
    ];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = Str::uuid()->toString();
            $model->created_by = auth()->id();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(RefundItem::class,'refund_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

}
