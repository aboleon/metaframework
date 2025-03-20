<?php

namespace MetaFramework\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

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
            $this->error("MetaFramework: unknown console command '" . $this->argument('argument') . "'");
        }
    }

    private function config()
    {
        $this->newLine();
        $this->comment('Publishing configuration...');
        $this->comment('------------------------------------------');

        $app_name = $this->ask("What is the name of your app");
        $app_default_lg = $this->ask("What is the app default language locale (en, fr, de..) ? Default is en", 'en');
        $panel_prefix = $this->ask("What is the prefix for your back-office routes");

        // Validate input
        if (empty($app_name) || empty($app_default_lg) || empty($panel_prefix)) {
            $this->error('All fields are required.');
            return;
        }

        // Update app.php configuration
        $this->updateConfigFile(config_path('app.php'), [
            "'name'" => "    'name' => '" . addslashes($app_name) . "',",
            "'locale'" => "    'locale' => '" . $app_default_lg . "',",
            "'fallback_locale'" => "    'fallback_locale' => '" . $app_default_lg . "',"
        ]);

        // Create mfw.php configuration
        $mfwConfigPath = base_path('config/mfw.php');
        $mfwConfigContent = "<?php
return [
    'route' => '" . $panel_prefix . "',
    'locales' => ['" . $app_default_lg . "'],
    'active_locales' => ['" . $app_default_lg . "']
];";

        if (!File::put($mfwConfigPath, $mfwConfigContent)) {
            $this->error('Failed to write mfw configuration file.');
            return;
        }

        // Update routes in bootstrap/app.php or routes/web.php
        $routeFilePath = base_path('routes/web.php'); // or base_path('routes/web.php')
        $this->replaceInFile("view('dashboard", "view('" . $panel_prefix . '/dashboard', $routeFilePath);

        $this->callPublishConfiguration();
    }

    /**
     * Update configuration file by replacing specific lines.
     *
     * @param string $filePath
     * @param array $replacements
     * @return void
     */
    private function updateConfigFile(string $filePath, array $replacements): void
    {
        if (!File::exists($filePath)) {
            $this->error('Configuration file not found: ' . $filePath);
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            $this->error('Failed to read configuration file: ' . $filePath);
            return;
        }

        foreach ($replacements as $search => $replace) {
            $seek = array_filter($lines, function ($line) use ($search) {
                return strstr($line, $search);
            });
            if (!empty($seek)) {
                $lines[key($seek)] = $replace;
            }
        }

        if (!File::put($filePath, implode("\n", $lines))) {
            $this->error('Failed to update configuration file: ' . $filePath);
        }
    }

    /**
     * Replace a string in a file.
     *
     * @param string $search
     * @param string $replace
     * @param string $filePath
     * @return void
     */
    private function replaceInFile(string $search, string $replace, string $filePath): void
    {
        if (!File::exists($filePath)) {
            $this->error('File not found: ' . $filePath);
            return;
        }

        $content = File::get($filePath);
        $newContent = str_replace($search, $replace, $content);

        if (!File::put($filePath, $newContent)) {
            $this->error('Failed to update file: ' . $filePath);
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
            '--tag' => 'mfw-install'
        ]);
    }
}