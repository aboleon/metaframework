<?php

declare(strict_types=1);

namespace MetaFramework\Support;

use Illuminate\Support\Facades\Schema;
use MetaFramework\Models\Role;
use Throwable;

final class UserRoles
{
    public const CORE_DEV_KEY = 'dev';

    public const CORE_SUPER_ADMIN_KEY = 'super-admin';

    /**
     * @return array<string, array{id:int,label:string,profile:string,subgroup:string}>
     */
    public static function core(): array
    {
        return [
            self::CORE_DEV_KEY => [
                'id' => 1,
                'label' => self::CORE_DEV_KEY,
                'profile' => self::CORE_DEV_KEY,
                'subgroup' => 'admin',
                'group_key' => 'admin',
                'is_system' => true,
            ],
            self::CORE_SUPER_ADMIN_KEY => [
                'id' => 2,
                'label' => self::CORE_SUPER_ADMIN_KEY,
                'profile' => 'admin',
                'subgroup' => 'admin',
                'group_key' => 'admin',
                'is_system' => true,
            ],
        ];
    }

    /**
     * @return array<string, array{slug:string,label:string,profile:string,subgroup:string,group_key:string,is_system:bool}>
     */
    public static function systemDefinitions(): array
    {
        return [
            self::CORE_DEV_KEY => [
                'slug' => self::CORE_DEV_KEY,
                'label' => self::CORE_DEV_KEY,
                'profile' => self::CORE_DEV_KEY,
                'subgroup' => 'admin',
                'group_key' => 'admin',
                'is_system' => true,
            ],
            self::CORE_SUPER_ADMIN_KEY => [
                'slug' => self::CORE_SUPER_ADMIN_KEY,
                'label' => self::CORE_SUPER_ADMIN_KEY,
                'profile' => 'admin',
                'subgroup' => 'admin',
                'group_key' => 'admin',
                'is_system' => true,
            ],
        ];
    }

    /**
     * @return array<string, array{id:int,label:string,profile:string,subgroup:string}>
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
     * @return array<string, array{id:int,label:string,profile:string,subgroup:string,group_key:string,is_system:bool}>
     */
    private static function fromDatabase(): array
    {
        $roles = [];
        foreach (Role::query()->orderBy('id')->get() as $role) {
            $roles[$role->slug] = [
                'id' => (int) $role->id,
                'label' => $role->label,
                'profile' => $role->profile ?: 'public',
                'subgroup' => $role->subgroup ?: 'public',
                'group_key' => $role->group_key ?: $role->subgroup ?: 'public',
                'is_system' => (bool) $role->is_system,
            ];
        }

        return $roles;
    }

    /**
     * @param  array<string, array{id:int,label:string,profile:string,subgroup:string,group_key?:string,is_system?:bool}>  $roles
     * @return array<string, array{id:int,label:string,profile:string,subgroup:string,group_key:string,is_system:bool}>
     */
    private static function withDefaultRole(array $roles): array
    {
        $defaultKey = array_key_exists(self::CORE_SUPER_ADMIN_KEY, $roles)
            ? self::CORE_SUPER_ADMIN_KEY
            : array_key_first($roles);

        if ($defaultKey !== null && isset($roles[$defaultKey])) {
            $defaultRole = $roles[$defaultKey];
            if (!isset($defaultRole['group_key'])) {
                $defaultRole['group_key'] = $defaultRole['subgroup'] ?? 'public';
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
