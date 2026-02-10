<?php

declare(strict_types=1);

namespace MetaFramework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    protected $table = 'users_roles';

    protected $guarded = [];

    public $timestamps = false;

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
