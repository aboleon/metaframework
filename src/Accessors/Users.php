<?php

declare(strict_types=1);

namespace MetaFramework\Accessors;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use MetaFramework\Support\UserRoles;

class Users
{
    private array $user_types;

    public function __construct()
    {
        $this->user_types = UserRoles::all();
    }

    public function usersOfType(string $type): Collection
    {
        return $this->availableRoles()->filter(static fn (array $role, string $key): bool => $key === $type);
    }

    public function adminUsers(): Collection
    {
        return $this->availableRoles()->only([
            UserRoles::CORE_DEV_KEY,
            UserRoles::CORE_SUPER_ADMIN_KEY,
        ]);
    }

    public function adminContact(): array
    {
        return $this->availableRoles()->get(UserRoles::CORE_SUPER_ADMIN_KEY)
            ?? $this->availableRoles()->first()
            ?? [];
    }

    public function publicUsers(): Collection
    {
        return $this->availableRoles()->where('group_key', 'public');
    }

    public function backendUsers(): Collection
    {
        return $this->availableRoles()->where('group_key', '!=', 'public');
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
        return $this->availableRoles()->toArray();
    }

    public function printRoles(Authenticatable $user): string
    {
        if ($user->roles->isNotEmpty()) {
            foreach ($user->roles as $role) {
                $roleLabel = (string) ($this->userType((int) $role->role_id)['label'] ?? $role->role_id);
                $translationKey = 'user_type.' . $roleLabel . '.label';
                $translatedRoleLabel = trans($translationKey);
                echo '<span class="role btn btn-sm btn-secondary">' . ($translatedRoleLabel === $translationKey ? $roleLabel : $translatedRoleLabel) . '</span>';
            }
        }

        return '';
    }

    private function availableRoles(): Collection
    {
        return collect($this->user_types)->except('default');
    }
}
