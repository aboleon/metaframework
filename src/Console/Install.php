<?php

namespace MetaFramework\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mfw {argument}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the MetaFramework';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (method_exists($this, $this->argument('argument'))) {
            $this->{$this->argument('argument')}();
        } else {
            $this->error("MetaFramework: unknown console command '".$this->argument('argument')."'");
        }
    }

    private function config()
    {
        $this->newLine();
        $this->comment('Publishing configuration...');
        $this->comment('------------------------------------------');

        $app_name       = $this->ask("What is the name of your app");
        $app_default_lg = $this->ask("What is the app default language locale (en, fr, de..) ? Default is en", 'en');
        $panel_prefix   = $this->ask("What is the prefix for your back-office routes");

        // Validate input
        if (empty($app_name) || empty($app_default_lg) || empty($panel_prefix)) {
            $this->error('All fields are required.');

            return;
        }

        // Update app.php configuration
        $this->updateConfigFile(config_path('app.php'), [
            "'name'"            => "    'name' => '".addslashes($app_name)."',",
            "'timezone'"        => "    'timezone' => 'Europe/Paris',",
            "'locale'"          => "    'locale' => '".$app_default_lg."',",
            "'fallback_locale'" => "    'fallback_locale' => '".$app_default_lg."',",
        ]);

        // Create mfw.php configuration
        $mfwConfigPath    = base_path('config/mfw.php');
        $mfwConfigContent = "<?php
return [
    'route' => '".$panel_prefix."',
    'locales' => ['".$app_default_lg."'],
    'active_locales' => ['".$app_default_lg."']
];";

        if ( ! File::put($mfwConfigPath, $mfwConfigContent)) {
            $this->error('Failed to write mfw configuration file.');

            return;
        }

        // Update routes in bootstrap/app.php or routes/web.php
        $routeFilePath = base_path('routes/web.php'); // or base_path('routes/web.php')
        $this->replaceInFile("view('dashboard", "view('".$panel_prefix.'/dashboard', $routeFilePath);

        $this->callPublishConfiguration();
        $this->setupUserFactoryAndSeeder();
    }

    private function auth()
    {
        $this->newLine();
        $this->comment('Publishing Auth Package...');
        $this->comment('------------------------------------------');

        $routeFilePath = base_path('routes/web.php');

        $auth_routes     = <<<'PHP'
            Route::get('/dashboard', function () {
                return view('dashboard');
            })->middleware(['auth', 'verified'])->name('dashboard');
            
            require __DIR__.'/auth.php';
            PHP;
        $existingContent = File::get($routeFilePath);

        $pattern = "/^use .*?;/m";
        preg_match_all($pattern, $existingContent, $matches);

        if ( ! empty($matches[0])) {
            $lastUseStatement = end($matches[0]);
            $position         = strrpos($existingContent, $lastUseStatement) + strlen($lastUseStatement);

            $newContent = substr($existingContent, 0, $position).PHP_EOL.PHP_EOL.$auth_routes.substr($existingContent, $position);

            File::put($routeFilePath, $newContent);
        } else {
            File::append($routeFilePath, "\n".$auth_routes);
        }

        $this->callAuthPackage();

        $this->comment('Auth Package published successfully.');
    }

    /**
     * Update configuration file by replacing specific lines.
     *
     * @param  string  $filePath
     * @param  array   $replacements
     *
     * @return void
     */
    private function updateConfigFile(string $filePath, array $replacements): void
    {
        if ( ! File::exists($filePath)) {
            $this->error('Configuration file not found: '.$filePath);

            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            $this->error('Failed to read configuration file: '.$filePath);

            return;
        }

        foreach ($replacements as $search => $replace) {
            $seek = array_filter($lines, function ($line) use ($search) {
                return strstr($line, $search);
            });
            if ( ! empty($seek)) {
                $lines[key($seek)] = $replace;
            }
        }

        if ( ! File::put($filePath, implode("\n", $lines))) {
            $this->error('Failed to update configuration file: '.$filePath);
        }
    }

    /**
     * Replace a string in a file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $filePath
     *
     * @return void
     */
    private function replaceInFile(string $search, string $replace, string $filePath): void
    {
        if ( ! File::exists($filePath)) {
            $this->error('File not found: '.$filePath);

            return;
        }

        $content    = File::get($filePath);
        $newContent = str_replace($search, $replace, $content);

        if ( ! File::put($filePath, $newContent)) {
            $this->error('Failed to update file: '.$filePath);
        }
    }

    /**
     * Publish configuration files.
     *
     * @return void
     */
    private function callPublishConfiguration(): void
    {
        $this->call('vendor:publish', [
            '--provider' => 'MetaFramework\ServiceProvider',
            '--tag'      => 'mfw-install',
        ]);
    }

    /**
     * Publish auth files.
     *
     * @return void
     */
    private function callAuthPackage(): void
    {
        $this->call('vendor:publish', [
            '--provider' => 'MetaFramework\ServiceProvider',
            '--tag'      => 'mfw-auth',
        ]);
    }

    private function setupUserFactoryAndSeeder(): void
    {
        $this->newLine();
        $this->comment('Configuring default admin user and authentication scaffolding...');
        $this->comment('------------------------------------------');

        $this->publishUserFactoryStub();

        if (! $this->confirm('Would you like to configure a default admin user now?', true)) {
            $this->info('User factory has been updated. You can create your admin seeder later using: php artisan make:seeder AdminUserSeeder');
            return;
        }

        $firstName = $this->ask('Admin first name');
        $lastName  = $this->ask('Admin last name');
        $email     = $this->ask('Admin email address', 'admin@example.com');

        if (! $firstName || ! $lastName || ! $email) {
            $this->error('First name, last name and email are required to create the admin user.');
            return;
        }

        $password = $this->secret('Admin password (leave blank to auto-generate)');
        if (! $password) {
            $password = Str::password(12);
            $this->info('');
            $this->info('Generated password: ' . $password);
        }

        $rolesConfigPath = config_path('mfw-users.php');
        $rolesConfig = File::exists($rolesConfigPath) ? include $rolesConfigPath : [];
        $roleId = null;

        if (! empty($rolesConfig) && is_array($rolesConfig)) {
            $roleKeys = array_keys($rolesConfig);
            $defaultRole = array_key_exists('super-admin', $rolesConfig) ? 'super-admin' : $roleKeys[0];
            $roleKey = $this->anticipate('Role key for the admin user', $roleKeys, $defaultRole);
            $roleId = $rolesConfig[$roleKey]['id'] ?? null;

            if (! $roleId) {
                $this->warn("Role [$roleKey] does not define an id in config/mfw-users.php. The admin user will be created without a role assignment.");
            }
        } else {
            $this->warn('Unable to locate roles from config/mfw-users.php. The admin user will be created without a role assignment.');
        }

        $this->publishAdminSeederStub($firstName, $lastName, $email, $password, $roleId);
        $this->ensureDatabaseSeederCallsAdminSeeder();

        $this->newLine();
        $this->info('User factory and admin seeder have been configured.');
        $this->info('Remember to run: php artisan migrate --seed');
    }

    private function publishUserFactoryStub(): void
    {
        $stubPath = __DIR__ . '/../../publishables/stubs/database/factories/UserFactory.stub';
        if (! File::exists($stubPath)) {
            $this->error('User factory stub not found.');
            return;
        }

        $targetPath = database_path('factories/UserFactory.php');
        $directory = dirname($targetPath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($targetPath, File::get($stubPath));
    }

    private function publishAdminSeederStub(string $firstName, string $lastName, string $email, string $password, ?int $roleId = null): void
    {
        $stubPath = __DIR__ . '/../../publishables/stubs/database/seeders/AdminUserSeeder.stub';
        if (! File::exists($stubPath)) {
            $this->error('Admin user seeder stub not found.');
            return;
        }

        $replacements = [
            '{{ first_name }}' => addslashes($firstName),
            '{{ last_name }}' => addslashes($lastName),
            '{{ email }}' => addslashes($email),
            '{{ password }}' => addslashes($password),
            '{{ role_id }}' => $roleId !== null ? (string) $roleId : 'null',
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), File::get($stubPath));

        $targetPath = database_path('seeders/AdminUserSeeder.php');
        $directory = dirname($targetPath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($targetPath, $content);
    }

    private function ensureDatabaseSeederCallsAdminSeeder(): void
    {
        $databaseSeederPath = database_path('seeders/DatabaseSeeder.php');
        if (! File::exists($databaseSeederPath)) {
            $this->warn('DatabaseSeeder.php was not found; skipping automatic wiring of the admin seeder.');
            return;
        }

        $content = File::get($databaseSeederPath);

        if (str_contains($content, 'AdminUserSeeder::class')) {
            File::put($databaseSeederPath, $content);
            return;
        }

        $content = preg_replace('/\s*User::factory\(\)->create\(\[[\s\S]*?\]\);\s*/m', PHP_EOL, $content);

        if (! str_contains($content, 'User::')) {
            $content = str_replace('use App\\Models\\User;' . PHP_EOL, '', $content);
        }

        $pattern = '/public function run\(\): void\s*\{\s*/';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "$0        \$this->call(AdminUserSeeder::class);\n\n", $content, 1);
        } else {
            $this->warn('Unable to automatically update DatabaseSeeder.php; please ensure AdminUserSeeder::class is called manually.');
        }

        File::put($databaseSeederPath, $content);
    }
}
