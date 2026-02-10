<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use MetaFramework\Models\RoleGroup;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
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

        $adminGroupId = $this->ensureRoleGroup(RoleGroup::CORE_ADMIN_KEY, true);
        $publicGroupId = $this->ensureRoleGroup(RoleGroup::CORE_PUBLIC_KEY, true);

        if (!Schema::hasColumn('roles', 'group_id')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('group_id')->nullable()->after('label');
            });
        }

        if (Schema::hasColumn('roles', 'subgroup')) {
            $subgroups = DB::table('roles')
                ->select('subgroup')
                ->distinct()
                ->pluck('subgroup')
                ->map(static fn ($value): string => trim((string) $value))
                ->filter(static fn (string $value): bool => $value !== '')
                ->values();

            foreach ($subgroups as $subgroup) {
                $groupKey = Str::slug($subgroup);
                if ($groupKey === '') {
                    $groupKey = RoleGroup::CORE_PUBLIC_KEY;
                }
                $groupId = $this->ensureRoleGroup(
                    $groupKey,
                    in_array($groupKey, [RoleGroup::CORE_ADMIN_KEY, RoleGroup::CORE_PUBLIC_KEY], true),
                    $subgroup
                );

                DB::table('roles')
                    ->where('subgroup', $subgroup)
                    ->whereNull('group_id')
                    ->update([
                        'group_id' => $groupId,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('roles')->whereNull('group_id')->update([
                'group_id' => $publicGroupId ?: $adminGroupId,
                'updated_at' => now(),
            ]);

            Schema::table('roles', function (Blueprint $table): void {
                $table->dropColumn('subgroup');
            });
        } else {
            DB::table('roles')->whereNull('group_id')->update([
                'group_id' => $publicGroupId ?: $adminGroupId,
                'updated_at' => now(),
            ]);
        }

        $this->addForeignKeyIfMissing();
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        if (!Schema::hasColumn('roles', 'subgroup')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->string('subgroup')->default('public')->index();
            });
        }

        if (Schema::hasColumn('roles', 'group_id')) {
            $groups = Schema::hasTable('role_groups')
                ? DB::table('role_groups')->pluck('key', 'id')->all()
                : [];

            DB::table('roles')
                ->select('id', 'group_id')
                ->orderBy('id')
                ->chunkById(100, function ($rows) use ($groups): void {
                    foreach ($rows as $row) {
                        $subgroup = $groups[(int) $row->group_id] ?? RoleGroup::CORE_PUBLIC_KEY;
                        DB::table('roles')
                            ->where('id', $row->id)
                            ->update([
                                'subgroup' => $subgroup,
                                'updated_at' => now(),
                            ]);
                    }
                }, 'id');

            $this->dropForeignKeyIfPresent();
            Schema::table('roles', function (Blueprint $table): void {
                $table->dropColumn('group_id');
            });
        }
    }

    private function ensureRoleGroup(string $key, bool $isSystem, ?string $label = null): int
    {
        $now = now();
        $labelValue = $label ?: Str::headline($key);
        DB::table('role_groups')->updateOrInsert(
            ['key' => $key],
            [
                'label' => $this->asTranslatedPayload($labelValue),
                'description' => $this->asTranslatedPayload($labelValue),
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

    private function addForeignKeyIfMissing(): void
    {
        if (!Schema::hasColumn('roles', 'group_id')) {
            return;
        }

        try {
            Schema::table('roles', function (Blueprint $table): void {
                $table->index('group_id', 'roles_group_id_index');
            });
        } catch (Throwable) {
            // Index already exists.
        }

        try {
            Schema::table('roles', function (Blueprint $table): void {
                $table->foreign('group_id', 'roles_group_id_foreign')
                    ->references('id')
                    ->on('role_groups')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        } catch (Throwable) {
            // Foreign key already exists.
        }
    }

    private function dropForeignKeyIfPresent(): void
    {
        try {
            Schema::table('roles', function (Blueprint $table): void {
                $table->dropForeign('roles_group_id_foreign');
            });
        } catch (Throwable) {
            // Foreign key not present.
        }

        try {
            Schema::table('roles', function (Blueprint $table): void {
                $table->dropIndex('roles_group_id_index');
            });
        } catch (Throwable) {
            // Index not present.
        }
    }

};
