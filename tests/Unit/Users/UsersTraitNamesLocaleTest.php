<?php

declare(strict_types=1);

namespace Tests\Unit\Users;

use Illuminate\Database\Eloquent\Model;
use MetaFramework\Accessors\Locale;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;
use MetaFramework\Polyglote\Traits\Translation;
use MetaFramework\Traits\Users;
use ReflectionClass;
use Tests\TestCase;

class UsersTraitNamesLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setMultilangState(true);
    }

    public function test_names_uses_requested_locale_for_translatable_first_and_last_name(): void
    {
        $user = new TestTranslatableUserWithNames;
        $user->setTranslation('first_name', 'fr', 'Jean');
        $user->setTranslation('first_name', 'bg', 'Ivan');
        $user->setTranslation('last_name', 'fr', 'Dupont');
        $user->setTranslation('last_name', 'bg', 'Petrov');

        app()->setLocale('fr');

        $this->assertSame('Jean Dupont', $user->names());
        $this->assertSame('Ivan Petrov', $user->names('bg'));
    }

    public function test_names_falls_back_to_plain_values_for_non_translatable_models(): void
    {
        $user = new TestPlainUserWithNames;
        $user->first_name = 'John';
        $user->last_name = 'Doe';

        $this->assertSame('John Doe', $user->names('bg'));
    }

    private function setMultilangState(bool $multilang): void
    {
        config()->set('mfw.translatable.multilang', $multilang);
        cache()->forget('mfw.multilang');

        $reflection = new ReflectionClass(Locale::class);
        $cacheProperty = $reflection->getProperty('multilangCache');
        $cacheProperty->setAccessible(true);
        $cacheProperty->setValue(null, null);
    }
}

class TestTranslatableUserWithNames extends Model implements TranslatableInterface
{
    use Translation;
    use Users;

    protected $table = 'test_translatable_user_names';

    protected $guarded = [];

    public $timestamps = false;

    public function setTranslatables(): array
    {
        return [
            'first_name' => [
                'label' => 'First name',
            ],
            'last_name' => [
                'label' => 'Last name',
            ],
        ];
    }
}

class TestPlainUserWithNames extends Model
{
    use Users;

    protected $table = 'test_plain_user_names';

    protected $guarded = [];

    public $timestamps = false;
}
