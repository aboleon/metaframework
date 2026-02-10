<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Models\UserRole;
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
        return $this->availableRoles()->where('group_key', 'public');
    }

    public function backOfficeUsers(): Collection
    {
        return $this->availableRoles()->where('group_key', '!=', 'public');
    }

    public function usersOfType(string $type): Collection
    {
        return $this->availableRoles()->filter(static fn (array $role, string $key): bool => $key === $type);
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

    public function names(): string
    {
        return $this->first_name . ' ' . $this->last_name;
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
                $roleLabel = (string) ($this->userType((int) $role->role_id)['label'] ?? $role->role_id);
                $translationKey = 'user_type.' . $roleLabel . '.label';
                $translatedRoleLabel = trans($translationKey);
                echo '<span class="role btn btn-sm btn-secondary">' . ($translatedRoleLabel === $translationKey ? $roleLabel : $translatedRoleLabel) . '</span>';
            }
        }

        return '';
    }

    public function userRolesKeys(): array
    {
        return $this->roles->pluck('role_id')->map(static fn ($item): int => (int) $item)->unique()->values()->toArray();
    }

    public function userRole(): ?int
    {
        $role = $this->roles->first();

        return $role ? (int) $role->role_id : null;
    }

    public function belongsToSubgroup(string|array $group): bool
    {
        return $this->userTypeParser('group_key', $group);
    }

    public function hasRole(string|array $role, bool $test = false): bool
    {
        if (!$role) {
            return false;
        }

        $targets = $this->targetRoles($role);
        $targeted = $this->targetRoleIds($targets);
        $userRoles = $this->userRolesKeys();

        if ($test) {
            d($targets, 'Parsed roles from input');
            d($targeted, 'Targeted');
            d($userRoles, 'User Roles');
            d(array_intersect($targeted, $userRoles), 'CUT');
        }

        if ($this->shouldFallbackToAuthenticatedRoleAccess()) {
            return true;
        }

        return (bool) array_intersect($targeted, $userRoles);
    }

    private function userTypeParser(string $parser, string|array $group): bool
    {
        if (is_string($group)) {
            $collection = $this->availableRoles()->where($parser, '=', $group)->keys()->toArray();
        } else {
            $collection = $this->availableRoles()->whereIn($parser, $group)->keys()->toArray();
        }

        return $this->hasRole($collection);
    }

    private function targetRoles(string|array $role): array
    {
        if (is_string($role)) {
            $separator = str_contains($role, '|') ? '|' : ',';

            return array_values(array_filter(array_map(
                static fn ($item): string => trim(str_replace(["'", '"', '[', ']'], '', $item)),
                explode($separator, $role)
            )));
        }

        return array_values(array_filter($role, static fn ($item): bool => is_string($item) || is_numeric($item)));
    }

    private function targetRoleIds(string|array $role): array
    {
        $targets = is_array($role) ? $role : $this->targetRoles($role);
        $availableRoles = $this->availableRoles();
        $stringable = $availableRoles->filter(static function (array $item, string $key) use ($targets): bool {
            return in_array($key, $targets, true);
        })->pluck('id')->map(static fn ($item): int => (int) $item)->toArray();
        $numeric = collect($targets)->filter(static fn ($item): bool => is_numeric($item))->map(static fn ($item): int => (int) $item)->toArray();

        return array_values(array_unique(array_merge($stringable, $numeric)));
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

        return (string) $authenticatedUser->getKey() === (string) $this->getKey();
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
