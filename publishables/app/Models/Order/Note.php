<?php

namespace App\Models\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MetaFramework\Services\Validation\ValidationTrait;

class Note extends Model
{
    use HasFactory;
    use ValidationTrait;

    protected $table = 'order_notes';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
