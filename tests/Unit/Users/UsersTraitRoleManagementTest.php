<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Traits\Users;
use Tests\TestCase;

class UsersTraitRoleManagementTest extends TestCase
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

        Schema::create('test_users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        Schema::create('users_roles', function (Blueprint $table): void {
            $table->unsignedInteger('role_id')->index();
            $table->unsignedInteger('user_id')->index();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users_roles');
        Schema::dropIfExists('test_users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_groups');

        parent::tearDown();
    }

    public function test_core_roles_are_immutable_and_default_is_available(): void
    {
        $this->seedRoles();

        $user = new TestUserWithRoles;
        $roles = $user->userTypes();

        $this->assertSame(1, $roles['dev']['id']);
        $this->assertSame(2, $roles['super-admin']['id']);
        $this->assertSame(10, $roles['editor']['id']);
        $this->assertSame($roles['super-admin'], $roles['default']);
        $this->assertArrayNotHasKey('default', $user->user_roles());
    }

    public function test_has_role_supports_role_keys_and_numeric_ids(): void
    {
        $this->seedRoles();

        $user = TestUserWithRoles::query()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        DB::table('users_roles')->insert([
            'user_id' => $user->id,
            'role_id' => 10,
        ]);

        $freshUser = $user->fresh();
        $this->assertTrue($freshUser->hasRole('editor'));
        $this->assertTrue($freshUser->hasRole('10'));
        $this->assertTrue($freshUser->hasRole('editor|dev'));
        $this->assertFalse($freshUser->hasRole('dev'));
    }

    public function test_scope_with_role_filters_and_returns_empty_for_unknown_role(): void
    {
        $this->seedRoles();

        $editorUser = TestUserWithRoles::query()->create([
            'first_name' => 'Editor',
            'last_name' => 'One',
        ]);
        $devUser = TestUserWithRoles::query()->create([
            'first_name' => 'Dev',
            'last_name' => 'Two',
        ]);

        DB::table('users_roles')->insert([
            [
                'user_id' => $editorUser->id,
                'role_id' => 10,
            ],
            [
                'user_id' => $devUser->id,
                'role_id' => 1,
            ],
        ]);

        $editorIds = TestUserWithRoles::query()->withRole('editor')->pluck('id')->all();
        $this->assertSame([$editorUser->id], $editorIds);
        $this->assertSame(0, TestUserWithRoles::query()->withRole('unknown-role')->count());
    }

    public function test_all_roles_fallback_only_when_no_roles_exist_in_database(): void
    {
        $this->seedRoles();

        $authenticatedUser = TestUserWithRoles::query()->create([
            'first_name' => 'NoRole',
            'last_name' => 'Auth',
        ]);
        $otherUser = TestUserWithRoles::query()->create([
            'first_name' => 'NoRole',
            'last_name' => 'Other',
        ]);

        $this->be($authenticatedUser);

        $this->assertTrue($authenticatedUser->fresh()->hasRole('dev'));
        $this->assertTrue($authenticatedUser->fresh()->hasRole('super-admin'));
        $this->assertTrue($authenticatedUser->fresh()->hasRole('editor'));
        $this->assertFalse($otherUser->fresh()->hasRole('dev'));

        DB::table('users_roles')->insert([
            'user_id' => $otherUser->id,
            'role_id' => 1,
        ]);

        $this->assertFalse($authenticatedUser->fresh()->hasRole('dev'));
        $this->assertFalse($authenticatedUser->fresh()->hasRole('editor'));
    }

    private function seedRoles(): void
    {
        DB::table('role_groups')->insert([
            [
                'id' => 1,
                'key' => 'admin',
                'label' => 'admin',
                'description' => 'admin',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'key' => 'public',
                'label' => 'public',
                'description' => 'public',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('roles')->insert([
            [
                'id' => 1,
                'key' => 'dev',
                'label' => 'dev',
                'group_id' => 1,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'key' => 'super-admin',
                'label' => 'super-admin',
                'group_id' => 1,
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'key' => 'editor',
                'label' => 'editor',
                'group_id' => 1,
                'is_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

class TestUserWithRoles extends Authenticatable
{
    use Users;

    protected $table = 'test_users';

    protected $guarded = [];

    public $timestamps = false;
}
