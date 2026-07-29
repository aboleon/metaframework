<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use MetaFramework\ServiceProvider as MetaFrameworkServiceProvider;
use Tests\TestCase;

class TranslationsTest extends TestCase
{
    public function test_package_translation_groups_are_available_through_the_mfw_namespace(): void
    {
        $this->assertSame('Confirmer les migrations', __('mfw::mfw.migrate_confirm_title'));
        $this->assertSame('Français', __('mfw::mfw-lang.fr.label'));
        $this->assertSame('Accès refusé.', __('mfw::mfw-users.errors.access_denied'));
        $this->assertSame('Aucun enregistrement dans la base de données', __('mfw::errors.no_data_in_db'));
        $this->assertSame('Requête SQL', __('mfw::sql.title'));
    }

    public function test_application_can_override_one_package_line_without_copying_the_file(): void
    {
        $overrideDirectory = lang_path('vendor/mfw/fr');
        $overrideFile = $overrideDirectory.'/mfw.php';

        File::ensureDirectoryExists($overrideDirectory);
        File::put($overrideFile, <<<'PHP'
<?php

return [
    'migrate_confirm_title' => 'Confirmation personnalisée',
];
PHP);

        try {
            $this->assertSame('Confirmation personnalisée', __('mfw::mfw.migrate_confirm_title'));
            $this->assertSame(
                'Cette action va exécuter les migrations en attente. Voulez-vous continuer ?',
                __('mfw::mfw.migrate_confirm_message'),
            );
        } finally {
            File::deleteDirectory(lang_path('vendor/mfw'));
        }
    }

    public function test_translation_publication_targets_the_namespaced_application_directory(): void
    {
        $paths = LaravelServiceProvider::pathsToPublish(MetaFrameworkServiceProvider::class, 'mfw-lang');
        $installPaths = LaravelServiceProvider::pathsToPublish(MetaFrameworkServiceProvider::class, 'mfw-install');

        $this->assertSame(
            [realpath(__DIR__.'/../../src/Resources/lang') => lang_path('vendor/mfw')],
            array_combine(array_map('realpath', array_keys($paths)), array_values($paths)),
        );
        $this->assertNotContains(lang_path(), array_values($installPaths));
    }
}
