<?php

declare(strict_types=1);

namespace MetaFramework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;
use MetaFramework\Polyglote\Traits\Translation;

class RoleGroup extends Model implements TranslatableInterface
{
    use Translation;

    public const CORE_ADMIN_KEY = 'admin';

    public const CORE_PUBLIC_KEY = 'public';

    protected $fillable = [
        'key',
        'label',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function setTranslatables(): array
    {
        return [
            'label' => [
                'label' => 'mfw-users.role_groups.label',
                'required',
                'class' => 'col-12',
            ],
            'description' => [
                'label' => 'mfw-users.role_groups.description',
                'type' => 'textarea',
                'class' => 'col-12',
            ],
        ];
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'group_id');
    }
}
