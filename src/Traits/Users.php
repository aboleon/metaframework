<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MetaFramework\Models\UserRole;
use MetaFramework\Services\Validation\ValidationTrait;

trait Users
{
    use ValidationTrait;

    private ?int $against_user_id = null;

    private array $target_role;

    public function adminUsers(): Collection
    {
        return collect($this->userTypes())->whereIn('profile', ['admin', 'dev']);
    }

    public function publicUsers(): Collection
    {
        return collect($this->userTypes())->where('profile', 'public');
    }

    public function backOfficeUsers(): Collection
    {
        return collect($this->userTypes())->where('subgroup', '!=', 'public');
    }

    public function usersOfType(string $type): Collection
    {
        return collect($this->userTypes())->where('profile', $type);
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
        return config('mfw-users');
    }

    public function names(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function user_roles(): array
    {
        return $this->userTypes();
    }

    public function scopeWithRole($query, string|array|null $role = null)
    {
        if ($role) {

            if (is_string($role)) {
                $this->target_role[] = $role;
            } else {
                $this->target_role = $role;
            }

            $roles = collect($this->userTypes())->filter(function ($item, $key) {
                return in_array($key, $this->target_role);
            })->pluck('id')->toArray();

            $query->whereHas('roles', function (Builder $subQuery) use ($roles) {
                $subQuery->whereIn('role_id', $roles);
            });
        }
    }

    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function printRoles(): string
    {
        if ($this->roles->isNotEmpty()) {
            foreach ($this->roles as $role) {
                echo '<span class="role btn btn-sm btn-secondary">' . trans('user_type.' . $this->userType($role->role_id)['label'] . '.label') . '</span>';
            }
        }

        return '';
    }

    public function userRolesKeys(): array
    {
        return $this->roles->pluck('role_id')->toArray();
    }

    public function userRole(): ?int
    {
        return $this->roles->first()?->role_id;
    }

    public function belongsToSubgroup(string|array $group): bool
    {
        return $this->userTypeParser('subgroup', $group);
    }

    public function belongsToProfile(string|array $profile): bool
    {
        return $this->userTypeParser('profile', $profile);
    }

    public function hasRole(string|array $role, bool $test = false): bool
    {
        if (!$role) {
            return false;
        }

        if (is_string($role)) {
            $separator = str_contains($role, '|') ? '|' : ',';
            $this->target_role = array_map(fn ($x) => trim(str_replace(["'", '"', '[', ']'], '', $x)), explode($separator, $role));
        } else {
            $this->target_role = $role;
        }

        $stringables = collect($this->userTypes())->filter(function ($item, $key) {
            return in_array($key, $this->target_role);
        })->pluck('id')->toArray();
        $numerics = collect($this->target_role)->reject(fn ($item) => !is_numeric($item))->toArray();
        $targeted = array_unique(array_merge($stringables, $numerics));

        if ($test) {
            d($this->target_role, 'Parsed roles from input');
            d($stringables, 'Stringables');
            d($numerics, 'Numerics');
            d($targeted, 'Targeted');
            d($this->userRolesKeys(), 'User Roles');
            d(array_intersect($targeted, $this->userRolesKeys()), 'CUT');
        }

        return (bool) array_intersect($targeted, $this->userRolesKeys());
    }

    private function userTypeParser(string $parser, string|array $group): bool
    {
        if (is_string($group)) {
            $collection = collect($this->userTypes())->where($parser, '=', $group)->keys()->toArray();
        } else {
            $collection = collect($this->userTypes())->whereIn($parser, $group)->keys()->toArray();
        }

        return $this->hasRole($collection);
    }
}
