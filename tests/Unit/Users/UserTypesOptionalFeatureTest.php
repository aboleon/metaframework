<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Support\UserTypes;
use MetaFramework\Traits\TypedUser;
use Tests\TestCase;

class UserTypesOptionalFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('typed_users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->nullable();
            $table->string('type')->nullable()->index();
        });

        config()->set('mfw-user-types.column', 'type');
        config()->set('mfw-user-types.values', ['system', 'account']);
        config()->set('mfw-user-types.default', 'system');
        config()->set('mfw-user-types.guards', [
            'web' => 'system',
            'account' => 'account',
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('typed_users');
        EloquentModel::clearBootedModels();

        parent::tearDown();
    }

    public function test_credentials_are_not_modified_when_feature_is_disabled(): void
    {
        config()->set('mfw-user-types.enabled', false);

        $credentials = UserTypes::addToCredentials(['email' => 'john@doe.com'], 'account');

        $this->assertSame(['email' => 'john@doe.com'], $credentials);
    }

    public function test_credentials_are_augmented_from_guard_mapping_when_enabled(): void
    {
        config()->set('mfw-user-types.enabled', true);

        $credentials = UserTypes::addToCredentials(['email' => 'john@doe.com'], 'account');

        $this->assertSame('account', $credentials['type']);
    }

    public function test_typed_user_trait_assigns_default_and_applies_scope(): void
    {
        config()->set('mfw-user-types.enabled', true);
        EloquentModel::clearBootedModels();

        TypedSystemUser::query()->create([
            'email' => 'system@demo.test',
        ]);

        TypedNeutralUser::query()->create([
            'email' => 'account@demo.test',
            'type' => 'account',
        ]);

        $this->assertSame(1, TypedSystemUser::query()->count());
        $this->assertSame(
            'system',
            TypedSystemUser::withoutGlobalScopes()->where('email', 'system@demo.test')->value('type')
        );

        $neutral = new TypedNeutralUser;
        $neutral->assignUserType(guard: 'account');
        $this->assertSame('account', $neutral->type);
    }
}

class TypedSystemUser extends EloquentModel
{
    use TypedUser;

    protected $table = 'typed_users';

    protected $guarded = [];

    public $timestamps = false;

    protected static function typedUserScopeType(): ?string
    {
        return 'system';
    }
}

class TypedNeutralUser extends EloquentModel
{
    use TypedUser;

    protected $table = 'typed_users';

    protected $guarded = [];

    public $timestamps = false;
}
