<?php

declare(strict_types=1);

namespace MetaFramework\Actions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use MetaFramework\Http\Requests\RunArtisanCommandRequest;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Support\Traits\Ajax;
use MetaFramework\Support\UserRoles;
use Symfony\Component\Console\Input\StringInput;
use Throwable;

class DevMaintenanceActions
{
    use Ajax;
    use ValidationTrait;

    public function runArtisanCommand(): self
    {
        $this->validation(RunArtisanCommandRequest::class);

        try {
            $process = Process::path(base_path())->timeout(600);
            $pathPrefix = trim((string) config('mfw.shell_path_prefix'));

            if ($pathPrefix !== '') {
                $process->env([
                    'PATH' => rtrim($pathPrefix, '/\\') . PATH_SEPARATOR . (string) getenv('PATH'),
                ]);
            }

            $result = $process->run([
                'php',
                base_path('artisan'),
                ...(new StringInput((string) $this->validatedDataStringable('command')))->getRawTokens(),
                '--no-interaction',
            ]);
            $output = trim($result->output() . PHP_EOL . $result->errorOutput());

            if ($output !== '') {
                $this->responseNotice(nl2br(e($output)));
            }

            if ($result->failed()) {
                $this->responseError(__('mfw::mfw.dev.artisan.failed', [
                    'code' => $result->exitCode(),
                ]));

                return $this;
            }

            $this->responseSuccess(__('mfw::mfw.dev.artisan.success'));
        } catch (Throwable $exception) {
            $this->responseException($exception, __('mfw::mfw.dev.artisan.exception'));
        }

        return $this;
    }

    public function restartQueueWorkers(): self
    {
        if (! $this->canRunMaintenanceActions()) {
            $this->responseError(__('mfw::mfw.dev.unauthorized'));

            return $this;
        }

        try {
            if (Artisan::call('queue:restart') !== 0) {
                $this->responseError(__('mfw::mfw.dev.queue_restart_failure'));

                return $this;
            }

            $this->responseSuccess(__('mfw::mfw.dev.queue_restart_success'));
        } catch (Throwable $exception) {
            $this->responseException($exception, __('mfw::mfw.dev.queue_restart_exception'));
        }

        return $this;
    }

    private function canRunMaintenanceActions(): bool
    {
        $user = auth()->user();

        return (bool) $user
            && ((method_exists($user, 'isDev') && $user->isDev())
                || (method_exists($user, 'hasRole') && $user->hasRole(UserRoles::CORE_DEV_KEY)));
    }
}
