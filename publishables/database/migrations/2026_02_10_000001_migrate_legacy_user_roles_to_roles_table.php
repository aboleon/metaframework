<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Support\UserRoles;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users_roles')) {
            return;
        }

        if (!Schema::hasTable('role_groups')) {
            Schema::create('role_groups', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->longText('label');
                $table->longText('description')->nullable();
                $table->boolean('is_system')->default(false)->index();
                $table->timestamps();
            });
        }

        $this->ensureRoleGroup('admin', true);
        $this->ensureRoleGroup('public', true);

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->longText('label');
                $table->foreignId('group_id')->constrained('role_groups')->cascadeOnUpdate()->restrictOnDelete();
                $table->boolean('is_system')->default(false)->index();
                $table->timestamps();
            });
        }

        $now = now();
        $groups = DB::table('role_groups')->pluck('id', 'key')->all();
        $hasGroupId = Schema::hasColumn('roles', 'group_id');
        $hasSubgroup = Schema::hasColumn('roles', 'subgroup');
        foreach (UserRoles::systemDefinitions() as $role) {
            $payload = [
                'label' => $role['label'],
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($hasGroupId) {
                $payload['group_id'] = $groups[$role['group_key']] ?? $groups['admin'] ?? 1;
            } elseif ($hasSubgroup) {
                $payload['subgroup'] = $role['group_key'];
            }

            DB::table('roles')->updateOrInsert(
                ['key' => $role['key']],
                $payload
            );
        }

        $existingRows = DB::table('users_roles')
            ->select('user_id', 'role_id')
            ->whereNotNull('user_id')
            ->whereNotNull('role_id')
            ->get()
            ->unique(static fn (object $row): string => ((string) $row->user_id) . '-' . ((string) $row->role_id))
            ->values();

        $roleIds = $existingRows->pluck('role_id')
            ->map(static fn ($item): int => (int) $item)
            ->filter(static fn (int $item): bool => $item > 0)
            ->unique()
            ->values()
            ->all();

        if ($roleIds !== []) {
            $knownIds = DB::table('roles')
                ->whereIn('id', $roleIds)
                ->pluck('id')
                ->map(static fn ($item): int => (int) $item)
                ->all();

            $missingRoleIds = array_values(array_diff($roleIds, $knownIds));
            foreach ($missingRoleIds as $roleId) {
                $legacyGroupId = $this->ensureRoleGroup('legacy', false);
                $payload = [
                    'id' => $roleId,
                    'key' => 'legacy-role-' . $roleId,
                    'label' => 'legacy-role-' . $roleId,
                    'is_system' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($hasGroupId) {
                    $payload['group_id'] = $legacyGroupId;
                } elseif ($hasSubgroup) {
                    $payload['subgroup'] = 'legacy';
                }

                DB::table('roles')->insert($payload);
            }
        }

        if (Schema::hasTable('users_roles_mfw_tmp')) {
            Schema::drop('users_roles_mfw_tmp');
        }

        Schema::create('users_roles_mfw_tmp', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['user_id', 'role_id']);
        });

        if ($existingRows->isNotEmpty()) {
            DB::table('users_roles_mfw_tmp')->insert(
                $existingRows->map(static function (object $row): array {
                    return [
                        'role_id' => (int) $row->role_id,
                        'user_id' => (int) $row->user_id,
                    ];
                })->all()
            );
        }

        Schema::disableForeignKeyConstraints();
        Schema::drop('users_roles');
        Schema::rename('users_roles_mfw_tmp', 'users_roles');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally left empty: this migration is a one-way structural upgrade.
    }

    private function ensureRoleGroup(string $key, bool $isSystem): int
    {
        $now = now();
        DB::table('role_groups')->updateOrInsert(
            ['key' => $key],
            [
                'label' => $this->asTranslatedPayload($key),
                'description' => $this->asTranslatedPayload($key),
                'is_system' => $isSystem,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('role_groups')->where('key', $key)->value('id');
    }

    private function asTranslatedPayload(string $value): string
    {
        $fallbackLocale = (string) config('app.fallback_locale', 'en');
        $locale = (string) config('app.locale', $fallbackLocale);
        if ($fallbackLocale === '') {
            $fallbackLocale = 'en';
        }
        if ($locale === '') {
            $locale = $fallbackLocale;
        }

        return (string) json_encode([
            $locale => $value,
            $fallbackLocale => $value,
        ], JSON_UNESCAPED_UNICODE);
    }
};
