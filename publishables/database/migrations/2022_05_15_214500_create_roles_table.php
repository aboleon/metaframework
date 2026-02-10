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
