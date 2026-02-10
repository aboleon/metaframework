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

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('slug')->unique();
                $table->string('label');
                $table->string('profile')->default('public')->index();
                $table->string('subgroup')->default('public')->index();
                $table->string('group_key')->default('public')->index();
                $table->boolean('is_system')->default(false)->index();
                $table->timestamps();
            });
        }

        $now = now();
        foreach (UserRoles::systemDefinitions() as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                [
                    'label' => $role['label'],
                    'profile' => $role['profile'],
                    'subgroup' => $role['subgroup'],
                    'group_key' => $role['group_key'],
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
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
                DB::table('roles')->insert([
                    'id' => $roleId,
                    'slug' => 'legacy-role-' . $roleId,
                    'label' => 'legacy-role-' . $roleId,
                    'profile' => 'public',
                    'subgroup' => 'legacy',
                    'group_key' => 'legacy',
                    'is_system' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
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
};
