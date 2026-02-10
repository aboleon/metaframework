<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Support\UserRoles;
use Tests\TestCase;

class UserRolesDatabaseResolutionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('role_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_groups');

        parent::tearDown();
    }

    public function test_it_prefers_database_roles_when_roles_table_exists(): void
    {
        DB::table('role_groups')->insert([
            [
                'id' => 1,
                'key' => 'admin',
                'label' => 'Admin',
                'description' => 'Admin',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'key' => 'content',
                'label' => 'Content',
                'description' => 'Content',
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('roles')->insert([
            [
                'id' => 1,
                'key' => 'dev',
                'label' => 'Developer',
                'group_id' => 1,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'key' => 'super-admin',
                'label' => 'Super Admin',
                'group_id' => 1,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'key' => 'editor',
                'label' => 'Editor',
                'group_id' => 2,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $roles = UserRoles::all();

        $this->assertSame('Developer', $roles['dev']['label']);
        $this->assertSame(5, $roles['editor']['id']);
        $this->assertSame('content', $roles['editor']['group_key']);
        $this->assertSame($roles['super-admin']['id'], $roles['default']['id']);
    }

    public function test_it_falls_back_to_core_roles_when_database_roles_have_empty_keys(): void
    {
        DB::table('roles')->insert([
            'id' => 50,
            'key' => '',
            'label' => 'Invalid',
            'group_id' => null,
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $roles = UserRoles::all();

        $this->assertArrayHasKey('dev', $roles);
        $this->assertArrayHasKey('super-admin', $roles);
        $this->assertSame($roles['super-admin']['id'], $roles['default']['id']);
    }
}
