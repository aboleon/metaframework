<?php

declare(strict_types=1);

namespace MetaFramework\Accessors;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

class Users
{
    private array $user_types;

    public function __construct()
    {
        $this->user_types = config('mfw-users');
    }

    public function usersOfType(string $type): Collection
    {
        return collect($this->user_types)->where('profile', $type);
    }

    public function adminUsers(): Collection
    {
        return collect($this->user_types)->whereIn('profile', ['admin', 'dev']);
    }

    public function adminContact(): array
    {
        return collect($this->user_types)->where('profile', 'admin')->first();
    }

    public function publicUsers(): Collection
    {
        return collect($this->user_types)->where('profile', 'public');
    }

    public function backendUsers(): Collection
    {
        return collect($this->user_types)->where('subgroup', '!=', 'public');
    }

    public function userTypes(): array
    {
        return $this->user_types ?? [];
    }

    public function userType(string|int|null $type = null): array
    {
        if (is_numeric($type)) {
            return collect($this->user_types)->where('id', $type)->first() ?: $this->user_types['default'];
        }

        return $this->user_types[$type] ?? $this->user_types['default'];
    }

    public function user_roles(): array
    {
        return $this->user_types;
    }

    public function printRoles(Authenticatable $user): string
    {
        if ($user->roles->isNotEmpty()) {
            foreach ($user->roles as $role) {
                echo '<span class="role btn btn-sm btn-secondary">' . trans('user_type.' . $this->userType($role->role_id)['label'] . '.label') . '</span>';
            }
        }

        return '';
    }
}
