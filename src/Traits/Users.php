<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Models\RoleGroup;
use MetaFramework\Models\UserRole;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Support\UserRoles;
use Throwable;

trait Users
{
    use ValidationTrait;

    public function adminUsers(): Collection
    {
        return $this->availableRoles()->only([
            UserRoles::CORE_DEV_KEY,
            UserRoles::CORE_SUPER_ADMIN_KEY,
        ]);
    }

    public function publicUsers(): Collection
    {
        $publicGroupKey = RoleGroup::CORE_PUBLIC_KEY;
        $publicGroupIds = $this->resolveGroupIds([$publicGroupKey]);

        return $this->availableRoles()->filter(
            fn(array $role): bool => $this->roleMatchesGroup($role, $publicGroupIds),
        );
    }

    public function backOfficeUsers(): Collection
    {
        $publicGroupKey = RoleGroup::CORE_PUBLIC_KEY;
        $publicGroupIds = $this->resolveGroupIds([$publicGroupKey]);

        return $this->availableRoles()->filter(
            fn(array $role): bool => !$this->roleMatchesGroup($role, $publicGroupIds),
        );
    }

    public function usersOfType(string $type): Collection
    {
        return $this->availableRoles()->filter(static fn(array $role, string $key): bool => $key === $type);
    }

    public function userType(string|int|null $type = null): array
    {
        if (is_numeric($type)) {
            return collect($this->userTypes())->where('id', $type)->first() ?: $this->userTypes()['default'];
        }

        return $this->userTypes()[$type] ?? $this->userTypes()['default'];
    }

    public function userTypes(): array
    {
        return UserRoles::all();
    }

    /**
     * Returns users first and last name, possibly localized
     * @param  string|null  $locale
     *
     * @return string
     */

    public function names(?string $locale = null): string
    {
        $names = trim((string)($this->first_name ?? '').' '.(string)($this->last_name ?? '')) ?: 'NC';

        if (!$locale || $locale === app()->getLocale()) {
            return $names;
        }

        if (is_subclass_of(static::class, TranslatableInterface::class) && !method_exists($this, 'getTranslation')) {
            return $names;
        }

        if (!method_exists($this, 'isTranslatableAttribute')
            || !$this->isTranslatableAttribute('first_name')
            || !$this->isTranslatableAttribute('last_name')
        ) {
            return $names;
        }

        return trim((string)$this->getTranslation('first_name', $locale).' '.(string)$this->getTranslation('last_name', $locale));
    }

    public function user_roles(): array
    {
        return $this->availableRoles()->toArray();
    }

    public function scopeWithRole(Builder $query, string|array|null $role = null): Builder
    {
        if ($role) {
            $roles = $this->targetRoleIds($role);
            if (empty($roles)) {
                $query->whereRaw('1 = 0');

                return $query;
            }

            $query->whereHas('roles', function (Builder $subQuery) use ($roles) {
                $subQuery->whereIn('role_id', $roles);
            });
        }

        return $query;
    }

    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function printRoles(): string
    {
        if ($this->roles->isNotEmpty()) {
            foreach ($this->roles as $role) {
                $roleLabel           = (string)($this->userType((int)$role->role_id)['label'] ?? $role->role_id);
                $translationKey      = 'user_type.'.$roleLabel.'.label';
                $translatedRoleLabel = trans($translationKey);
                echo '<span class="role btn btn-sm btn-secondary">'.($translatedRoleLabel === $translationKey ? $roleLabel : $translatedRoleLabel).'</span>';
            }
        }

        return '';
    }

    public function userRolesKeys(): array
    {
        return $this->roles->pluck('role_id')->map(static fn($item): int => (int)$item)->unique()->values()->toArray();
    }

    public function userRole(): ?int
    {
        $role = $this->roles->first();

        return $role ? (int)$role->role_id : null;
    }

    public function belongsToSubgroup(string|array $group): bool
    {
        $groupRoleKeys = $this->targetRoleKeysByGroup($group);

        return $this->hasRole($groupRoleKeys);
    }

    public function hasRole(string|array $role, bool $test = false): bool
    {
        unset($test);

        if (!$role) {
            return false;
        }

        $targets   = $this->targetRoles($role);
        $targeted  = $this->targetRoleIds($targets);
        $userRoles = $this->userRolesKeys();

        if ($this->shouldFallbackToAuthenticatedRoleAccess()) {
            return true;
        }

        return (bool)array_intersect($targeted, $userRoles);
    }

    private function targetRoles(string|array $role): array
    {
        if (is_string($role)) {
            $separator = str_contains($role, '|') ? '|' : ',';

            return array_values(
                array_filter(
                    array_map(
                        static fn($item): string => trim(str_replace(["'", '"', '[', ']'], '', $item)),
                        explode($separator, $role),
                    ),
                ),
            );
        }

        return array_values(array_filter($role, static fn($item): bool => is_string($item) || is_numeric($item)));
    }

    private function targetRoleIds(string|array $role): array
    {
        $targets        = is_array($role) ? $role : $this->targetRoles($role);
        $availableRoles = $this->availableRoles();
        $stringable     = $availableRoles->filter(static function (array $item, string $key) use ($targets): bool {
            return in_array($key, $targets, true);
        })->pluck('id')->map(static fn($item): int => (int)$item)->toArray();
        $numeric        = collect($targets)->filter(static fn($item): bool => is_numeric($item))->map(static fn($item): int => (int)$item)->toArray();

        return array_values(array_unique(array_merge($stringable, $numeric)));
    }

    private function targetRoleKeysByGroup(string|array $group): array
    {
        $targets  = $this->targetRoles($group);
        $groupIds = $this->resolveGroupIds($targets);

        return $this->availableRoles()->filter(
            fn(array $role): bool => $this->roleMatchesGroup($role, $groupIds),
        )->keys()->values()->toArray();
    }

    /**
     * @param  array<int, int|string>  $targets
     *
     * @return array<int, int>
     */
    private function resolveGroupIds(array $targets): array
    {
        $numeric = collect($targets)
            ->filter(static fn($item): bool => is_numeric($item))
            ->map(static fn($item): int => (int)$item)
            ->values()
            ->toArray();
        $keys    = array_values(array_filter($targets, static fn($item): bool => is_string($item) && !is_numeric($item)));

        if ($keys === []) {
            return array_values(array_unique($numeric));
        }

        try {
            if (!Schema::hasTable('role_groups')) {
                return array_values(array_unique($numeric));
            }

            $resolved = RoleGroup::query()
                ->whereIn('key', $keys)
                ->pluck('id')
                ->map(static fn($item): int => (int)$item)
                ->values()
                ->toArray();

            return array_values(array_unique(array_merge($numeric, $resolved)));
        } catch (Throwable) {
            return array_values(array_unique($numeric));
        }
    }

    /**
     * @param  array{id?:int,key?:string,label?:string,group_id?:int|null,is_system?:bool}  $role
     * @param  array<int, int>                                                              $groupIds
     */
    private function roleMatchesGroup(array $role, array $groupIds): bool
    {
        $groupId = $role['group_id'] ?? null;

        return is_numeric($groupId) && in_array((int)$groupId, $groupIds, true);
    }

    private function availableRoles(): Collection
    {
        return collect($this->userTypes())->except('default');
    }

    private function shouldFallbackToAuthenticatedRoleAccess(): bool
    {
        return !$this->anyRoleAssignmentExists() && $this->isCurrentAuthenticatedUser();
    }

    private function isCurrentAuthenticatedUser(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $authenticatedUser = auth()->user();
        if (!$authenticatedUser || !method_exists($authenticatedUser, 'getKey') || !method_exists($this, 'getKey')) {
            return false;
        }

        return (string)$authenticatedUser->getKey() === (string)$this->getKey();
    }

    private function anyRoleAssignmentExists(): bool
    {
        try {
            if (!Schema::hasTable('users_roles')) {
                return false;
            }

            return UserRole::query()->exists();
        } catch (Throwable) {
            return false;
        }
    }
}
