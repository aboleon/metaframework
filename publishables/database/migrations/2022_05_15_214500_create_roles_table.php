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
        foreach (UserRoles::systemDefinitions() as $role) {
            DB::table('roles')->updateOrInsert(
                ['key' => $role['key']],
                [
                    'label' => $role['label'],
                    'group_id' => $groups[$role['group_key']] ?? $groups['public'] ?? $groups['admin'] ?? 1,
                    'is_system' => $role['is_system'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
