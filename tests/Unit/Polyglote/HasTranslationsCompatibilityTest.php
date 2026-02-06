<?php

declare(strict_types=1);

namespace Tests\Unit\Polyglote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use MetaFramework\Accessors\Locale;
use MetaFramework\Polyglote\Events\TranslationHasBeenSetEvent;
use MetaFramework\Polyglote\Interfaces\TranslatableInterface;
use MetaFramework\Polyglote\Traits\Translation;
use ReflectionClass;
use Tests\TestCase;

class HasTranslationsCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_translatable_models', function (Blueprint $table): void {
            $table->increments('id');
            $table->text('name')->nullable();
            $table->text('description')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_translatable_models');

        parent::tearDown();
    }

    public function test_it_reads_and_writes_translations_when_multilang_is_enabled(): void
    {
        $this->setMultilangState(true);

        $model = new TestTranslatableModel;
        $model->setTranslation('name', 'fr', 'Bonjour');
        $model->setTranslation('name', 'bg', 'Zdravey');

        app()->setLocale('fr');
        $this->assertSame('Bonjour', $model->name);
        $this->assertSame('Zdravey', $model->getTranslation('name', 'en'));
        $this->assertSame(
            ['fr' => 'Bonjour', 'bg' => 'Zdravey'],
            $model->getTranslations('name')
        );
    }

    public function test_it_keeps_plain_attributes_when_multilang_is_disabled(): void
    {
        $this->setMultilangState(false);

        $model = new TestTranslatableModel;
        $model->name = 'Plain value';

        $this->assertSame('Plain value', $model->name);
        $this->assertSame('Plain value', $model->getAttributes()['name']);
        $this->assertSame([], $model->getTranslations('name'));
    }

    public function test_it_only_adds_translatable_casts_when_multilang_is_enabled(): void
    {
        $this->setMultilangState(true);
        $multiLangModel = new TestTranslatableModel;
        $this->assertArrayHasKey('name', $multiLangModel->getCasts());

        $this->setMultilangState(false);
        $singleLangModel = new TestTranslatableModel;
        $this->assertArrayNotHasKey('name', $singleLangModel->getCasts());
    }

    public function test_it_dispatches_metaframework_translation_event_via_bridge(): void
    {
        $this->setMultilangState(true);
        Event::fake([TranslationHasBeenSetEvent::class]);

        $model = new TestTranslatableModel;
        $model->setTranslation('name', 'fr', 'Bonjour');

        Event::assertDispatched(TranslationHasBeenSetEvent::class, function (TranslationHasBeenSetEvent $event): bool {
            return $event->key === 'name'
                && $event->locale === 'fr'
                && $event->newValue === 'Bonjour';
        });
    }

    private function setMultilangState(bool $multilang): void
    {
        config()->set('mfw.translatable.multilang', $multilang);
        Cache::forget('mfw.multilang');

        $reflection = new ReflectionClass(Locale::class);
        $cacheProperty = $reflection->getProperty('multilangCache');
        $cacheProperty->setAccessible(true);
        $cacheProperty->setValue(null, null);
    }
}

class TestTranslatableModel extends Model implements TranslatableInterface
{
    use Translation;

    protected $table = 'test_translatable_models';

    protected $fillable = [
        'name',
        'description',
    ];

    public $timestamps = false;

    public function setTranslatables(): array
    {
        return [
            'name' => [
                'label' => 'Name',
            ],
            'description' => [
                'label' => 'Description',
            ],
        ];
    }
}
