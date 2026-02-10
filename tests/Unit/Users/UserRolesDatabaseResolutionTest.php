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

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('profile')->default('public');
            $table->string('subgroup')->default('public');
            $table->string('group_key')->default('public');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('roles');

        parent::tearDown();
    }

    public function test_it_prefers_database_roles_when_roles_table_exists(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'slug' => 'dev',
                'label' => 'Developer',
                'profile' => 'dev',
                'subgroup' => 'admin',
                'group_key' => 'admin',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'slug' => 'super-admin',
                'label' => 'Super Admin',
                'profile' => 'admin',
                'subgroup' => 'admin',
                'group_key' => 'admin',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'slug' => 'editor',
                'label' => 'Editor',
                'profile' => 'admin',
                'subgroup' => 'content',
                'group_key' => 'content',
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
}
