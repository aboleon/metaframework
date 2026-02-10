<?php

declare(strict_types=1);

namespace MetaFramework\Support;

use Illuminate\Support\Facades\Schema;
use MetaFramework\Models\Role;
use MetaFramework\Models\RoleGroup;
use Throwable;

final class UserRoles
{
    public const CORE_DEV_KEY = 'dev';

    public const CORE_SUPER_ADMIN_KEY = 'super-admin';

    /**
     * @return array<string, array{id:int,key:string,label:string,group_id:int|null,group_key:string,is_system:bool}>
     */
    public static function core(): array
    {
        return [
            self::CORE_DEV_KEY => [
                'id' => 1,
                'key' => self::CORE_DEV_KEY,
                'label' => self::CORE_DEV_KEY,
                'group_id' => null,
                'group_key' => RoleGroup::CORE_ADMIN_KEY,
                'is_system' => true,
            ],
            self::CORE_SUPER_ADMIN_KEY => [
                'id' => 2,
                'key' => self::CORE_SUPER_ADMIN_KEY,
                'label' => self::CORE_SUPER_ADMIN_KEY,
                'group_id' => null,
                'group_key' => RoleGroup::CORE_ADMIN_KEY,
                'is_system' => true,
            ],
        ];
    }

    /**
     * @return array<string, array{key:string,label:string,group_key:string,is_system:bool}>
     */
    public static function systemDefinitions(): array
    {
        return [
            self::CORE_DEV_KEY => [
                'key' => self::CORE_DEV_KEY,
                'label' => self::CORE_DEV_KEY,
                'group_key' => RoleGroup::CORE_ADMIN_KEY,
                'is_system' => true,
            ],
            self::CORE_SUPER_ADMIN_KEY => [
                'key' => self::CORE_SUPER_ADMIN_KEY,
                'label' => self::CORE_SUPER_ADMIN_KEY,
                'group_key' => RoleGroup::CORE_ADMIN_KEY,
                'is_system' => true,
            ],
        ];
    }

    /**
     * @return array<string, array{id:int,key:string,label:string,group_id:int|null,group_key:string,is_system:bool}>
     */
    public static function all(): array
    {
        if (self::rolesTableExists()) {
            $databaseRoles = self::fromDatabase();
            if ($databaseRoles !== []) {
                return self::withDefaultRole($databaseRoles);
            }
        }

        return self::withDefaultRole(self::core());
    }

    /**
     * @return array<string, array{id:int,key:string,label:string,group_id:int|null,group_key:string,is_system:bool}>
     */
    private static function fromDatabase(): array
    {
        $roles = [];
        foreach (Role::query()->with('group')->orderBy('id')->get() as $role) {
            $roleKey = trim((string) $role->key);
            if ($roleKey === '') {
                continue;
            }

            $roles[$roleKey] = [
                'id' => (int) $role->id,
                'key' => $roleKey,
                'label' => self::resolveRoleLabel($role),
                'group_id' => $role->group_id ? (int) $role->group_id : null,
                'group_key' => $role->group?->key ?: RoleGroup::CORE_PUBLIC_KEY,
                'is_system' => (bool) $role->is_system,
            ];
        }

        return $roles;
    }

    private static function resolveRoleLabel(Role $role): string
    {
        $raw = $role->getRawOriginal('label');
        if (!is_string($raw) || trim($raw) === '') {
            $raw = (string) $role->label;
        }
        if (!is_string($raw) || trim($raw) === '') {
            return '';
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $currentLocale = (string) app()->getLocale();
            $fallbackLocale = (string) config('app.fallback_locale', 'en');

            $resolved = $decoded[$currentLocale]
                ?? $decoded[$fallbackLocale]
                ?? reset($decoded);

            return is_string($resolved) ? $resolved : '';
        }

        return $raw;
    }

    /**
     * @param  array<string, array{id:int,key:string,label:string,group_id:int|null,group_key:string,is_system?:bool}>  $roles
     * @return array<string, array{id:int,key:string,label:string,group_id:int|null,group_key:string,is_system:bool}>
     */
    private static function withDefaultRole(array $roles): array
    {
        $defaultKey = array_key_exists(self::CORE_SUPER_ADMIN_KEY, $roles)
            ? self::CORE_SUPER_ADMIN_KEY
            : array_key_first($roles);

        if ($defaultKey !== null && isset($roles[$defaultKey])) {
            $defaultRole = $roles[$defaultKey];
            if (!isset($defaultRole['key'])) {
                $defaultRole['key'] = $defaultKey;
            }
            if (!isset($defaultRole['is_system'])) {
                $defaultRole['is_system'] = in_array($defaultKey, [self::CORE_DEV_KEY, self::CORE_SUPER_ADMIN_KEY], true);
            }
            $roles['default'] = $defaultRole;
        }

        return $roles;
    }

    private static function rolesTableExists(): bool
    {
        try {
            return Schema::hasTable('roles');
        } catch (Throwable) {
            return false;
        }
    }
}
