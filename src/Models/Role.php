<?php

declare(strict_types=1);

namespace MetaFramework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;
use MetaFramework\Polyglote\Traits\Translation;

class Role extends Model implements TranslatableInterface
{
    use Translation;

    protected $fillable = [
        'key',
        'label',
        'group_id',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'group_id' => 'integer',
            'is_system' => 'boolean',
        ];
    }

    public function setTranslatables(): array
    {
        return [
            'label' => [
                'label' => 'mfw::mfw-users.roles.label',
                'required',
                'class' => 'col-12',
            ],
        ];
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(RoleGroup::class, 'group_id');
    }
}
