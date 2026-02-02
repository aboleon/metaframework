<?php

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
            $this->responseSuccess("Le cache application a été réinitialisé");
        }

        return $this->fetchResponse();
    }

    public function migrate(bool $rollback = false): array
    {
        $this->executeCommand('migrate' . ($rollback ? ':rollback' : ''));
        if ($this->statusCode == 0) {
            $this->responseSuccess("Opération terminée.");
        }
        return $this->fetchResponse();
    }

    private function executeCommand(string $command): void
    {
        $this->statusCode = Artisan::call($command, [], $outputBuffer = new BufferedOutput());
        $response = nl2br(trim($outputBuffer->fetch(), "\r\n"));
        if ($this->statusCode == 0) {
            $this->responseNotice($response);
        } else {
            $this->responseError($response);
        }
    }

    public function composerUpdate(): array
    {
        return $this->composerUpdateProd();
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
        ]);
        return $this->fetchResponse();
    }

    private function executeShellCommand(array $command): void
    {
        $process = new Process($command, base_path(), [
            'TERM' => 'dumb',
            'COMPOSER_NO_INTERACTION' => '1',
            'COMPOSER_NO_PROGRESS' => '1',
            'COMPOSER_DISABLE_XDEBUG_WARN' => '1',
        ]);
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
            $this->responseNotice('<div style="' . $noticeStyle . '"><strong>Command:</strong> ' . $commandLine . '</div>');
            $this->responseNotice('<div style="' . $noticeStyle . '">' . $output . '</div>');
            if ($errorOutput !== '') {
                $this->responseNotice('<div style="' . $errorStyle . '">' . $errorOutput . '</div>');
            }
            if ($isSuccessful) {
                $this->responseSuccess("Composer update completed successfully.");
            } else {
                $this->responseError("Composer update failed.");
            }
        } catch (ProcessFailedException $exception) {
            $this->responseError(nl2br($exception->getMessage()));
        }
    }


}
