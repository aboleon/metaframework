<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $hasProfile = Schema::hasColumn('roles', 'profile');
        $hasGroupKey = Schema::hasColumn('roles', 'group_key');
        if (!$hasProfile && !$hasGroupKey) {
            return;
        }

        Schema::table('roles', function (Blueprint $table) use ($hasProfile, $hasGroupKey): void {
            if ($hasProfile) {
                $table->dropColumn('profile');
            }
            if ($hasGroupKey) {
                $table->dropColumn('group_key');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $hasProfile = Schema::hasColumn('roles', 'profile');
        $hasGroupKey = Schema::hasColumn('roles', 'group_key');
        if ($hasProfile && $hasGroupKey) {
            return;
        }

        Schema::table('roles', function (Blueprint $table) use ($hasProfile, $hasGroupKey): void {
            if (!$hasProfile) {
                $table->string('profile')->default('public')->index();
            }
            if (!$hasGroupKey) {
                $table->string('group_key')->default('public')->index();
            }
        });
    }
};
