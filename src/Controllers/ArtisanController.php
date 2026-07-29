<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Support\Facades\Artisan;
use MetaFramework\Support\Traits\Ajax;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ArtisanController
{
    use Ajax;

    private array $output = [];

    private int $statusCode;

    public function optimizeClear(): array
    {
        $this->executeCommand('optimize:clear');
        if ($this->statusCode == 0) {
            $this->responseSuccess('Le cache application a été réinitialisé');
        }

        return $this->fetchResponse();
    }

    public function migrate(bool $rollback = false, bool $confirmed = false): array
    {
        if ($rollback && ! $confirmed) {
            $this->responseError(__('mfw::mfw.migrate_rollback_confirmation_required'));

            return $this->fetchResponse();
        }

        $this->executeCommand('migrate'.($rollback ? ':rollback' : ''));
        if ($this->statusCode == 0) {
            $this->responseSuccess('Opération terminée.');
        }

        return $this->fetchResponse();
    }

    private function executeCommand(string $command): void
    {
        $options = [];
        if ($command === 'migrate' || $command === 'migrate:rollback') {
            // Avoid interactive confirmation in production.
            $options = ['--force' => true, '--no-interaction' => true];
        }
        $this->statusCode = Artisan::call($command, $options, $outputBuffer = new BufferedOutput);
        $response = nl2br(trim($outputBuffer->fetch(), "\r\n"));
        if ($this->statusCode == 0) {
            $this->responseNotice($response);
        } else {
            $this->responseError($response);
        }
    }

    public function composerUpdateDev(): array
    {
        $this->executeShellCommand([
            'composer',
            'update',
            '--no-progress',
            '--no-ansi',
        ]);

        return $this->fetchResponse();
    }

    public function composerUpdateProd(): array
    {
        $this->executeShellCommand([
            'composer',
            'update',
            '--no-dev',
            '--optimize-autoloader',
            '--no-interaction',
            '--no-progress',
            '--no-ansi',
        ], 'Composer update (no-dev) completed successfully.', 'Composer update (no-dev) failed.');

        return $this->fetchResponse();
    }

    public function composerInstallProd(): array
    {
        $this->executeShellCommand([
            'composer',
            'install',
            '--no-dev',
            '--optimize-autoloader',
            '--no-interaction',
            '--no-progress',
            '--no-ansi',
        ], 'Composer install (no-dev) completed successfully.', 'Composer install (no-dev) failed.');

        return $this->fetchResponse();
    }

    public function composerDumpAutoload(): array
    {
        $this->executeShellCommand([
            'composer',
            'dump-autoload',
            '--no-interaction',
            '--no-ansi',
        ], 'Composer dump-autoload completed successfully.', 'Composer dump-autoload failed.');

        return $this->fetchResponse();
    }

    private function executeShellCommand(
        array $command,
        string $successMessage = 'Composer update completed successfully.',
        string $failureMessage = 'Composer update failed.'
    ): void {
        $pathPrefix = trim((string) env('MF_SHELL_PATH_PREFIX', ''));
        $existingPath = (string) env('PATH', (string) getenv('PATH'));
        $processEnv = [
            'TERM' => 'dumb',
            'COMPOSER_NO_INTERACTION' => '1',
            'COMPOSER_NO_PROGRESS' => '1',
            'COMPOSER_DISABLE_XDEBUG_WARN' => '1',
        ];
        if ($pathPrefix !== '') {
            $processEnv['PATH'] = rtrim($pathPrefix, ':').':'.$existingPath;
        }

        $process = new Process($command, base_path(), $processEnv);
        $process->setTimeout(600); // Set timeout to 10 minutes
        $process->setTty(false);

        try {
            $process->start();

            $output = '';
            $errorOutput = '';

            $process->wait(function ($type, $buffer) use (&$output, &$errorOutput) {
                if ($type === Process::OUT) {
                    $output .= nl2br($buffer);
                } else {
                    $errorOutput .= nl2br($buffer);
                }
            });

            $output = preg_replace('/\x1b\[[0-9;]*[A-Za-z]/', '', $output ?? '');
            $errorOutput = preg_replace('/\x1b\[[0-9;]*[A-Za-z]/', '', $errorOutput ?? '');
            $isSuccessful = $process->isSuccessful();
            $commandLine = implode(' ', $command);
            $noticeStyle = 'font-size: 13px; line-height: 1.5; color: #212529; margin-bottom: 8px;';
            $errorStyle = $isSuccessful
                ? $noticeStyle
                : 'font-size: 13px; line-height: 1.5; color: #dc3545; margin-bottom: 8px;';
            $this->responseNotice('<div style="'.$noticeStyle.'"><strong>Command:</strong> '.$commandLine.'</div>');
            $this->responseNotice('<div style="'.$noticeStyle.'">'.$output.'</div>');
            if ($errorOutput !== '') {
                $this->responseNotice('<div style="'.$errorStyle.'">'.$errorOutput.'</div>');
            }
            $this->appendPhpPathHintIfMissing($output, $errorOutput);
            if ($isSuccessful) {
                $this->responseSuccess($successMessage);
            } else {
                $this->responseError($failureMessage);
            }
        } catch (ProcessFailedException $exception) {
            $this->responseError(nl2br($exception->getMessage()));
        }
    }

    private function appendPhpPathHintIfMissing(string $output, string $errorOutput): void
    {
        $plainText = html_entity_decode(strip_tags($output."\n".$errorOutput));
        if (! preg_match('/\/usr\/bin\/env:\s*[\'"]?php[\'"]?:\s*No such file or directory/i', $plainText)) {
            return;
        }

        $hintStyle = 'font-size: 13px; line-height: 1.5; color: #dc3545; margin-bottom: 8px;';
        $this->responseError(
            '<div style="'.$hintStyle.'">'
            .'Did you add <code>MF_SHELL_PATH_PREFIX=\'path-to-php\'</code> to your <code>.env</code> file? '
            .'Example for Plesk server: <code>/opt/plesk/php/8.5/bin</code>.'
            .'</div>'
        );
    }
}
