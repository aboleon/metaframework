<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = 'users_roles';
    }


}
