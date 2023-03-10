<?php

declare(strict_types=1);

namespace MetaFramework\Traits;

use Illuminate\Http\RedirectResponse;
use JetBrains\PhpStorm\Pure;
use Throwable;

trait Responses
{
    protected array $response = [];
    protected string|null $redirect_route = null;
    protected string|null $redirect_to = null;
    private bool $disable_responses = false;
    protected bool $disable_redirects = false;
    protected bool $ajax_mode = false;

    public function enableAjaxMode(): void
    {
        $this->ajax_mode = true;
    }

    public function disableAjaxMode(): void
    {
        $this->ajax_mode = false;
    }

    public function disableMessages(): static
    {
        $this->disable_responses = true;
        return $this;
    }

    public function enableMessages(): static
    {
        $this->disable_responses = false;
        return $this;
    }

    public function enabledMessages(): bool
    {
        return $this->disable_responses === false;
    }

    public function fetchResponse(): array
    {
        return $this->response;
    }

    public function disableRedirects(): static
    {
        $this->disable_redirects = true;
        return $this;
    }

    public function fetchErrorMessages(): array
    {
        if (array_key_exists($this->messagesKey(), $this->response)) {

            $this->response[$this->messagesKey()] = array_filter($this->response[$this->messagesKey()], fn($key) => array_filter($key, fn($key) => in_array($key, ['danger', 'warning']), ARRAY_FILTER_USE_KEY));

        }
        return $this->response;
    }

    public function fetchResponseElement(string $key)
    {
        return $this->response[$key] ?? null;
    }

    public function hasErrors(): bool
    {
        return array_key_exists('error', $this->response);
    }

    public function mustAbort(): bool
    {
        return array_key_exists('abort', $this->response);
    }

    #[Pure] public function canContinue(): bool
    {
        return !$this->mustAbort();
    }

    public function responseNotice($message): static
    {
        if ($this->enabledMessages()) {
            $this->response[$this->messagesKey()][]['info'] = $message;
        }
        return $this;
    }

    public function responseSuccess($message, string|int $key = ''): static
    {
        if ($this->enabledMessages()) {
            if (!empty($key)) {
                $this->response[$this->messagesKey()][$key]['success'] = $message;
            } else {
                $this->response[$this->messagesKey()][]['success'] = $message;
            }
            return $this;
        }
        return $this;
    }

    public function whitout(string $element): static
    {
        unset($this->response[$element]);
        return $this;
    }

    protected function responseError($message): void
    {
        if ($this->enabledMessages()) {
            $this->response['error'] = true;
            $this->response[$this->messagesKey()][]['danger'] = $message;
        }
    }

    protected function responseAbort($message): void
    {
        if ($this->enabledMessages()) {
            $this->response['abort'] = true;
            $this->response[$this->messagesKey()][]['danger'] = $message;
        }
    }

    protected function responseWarning($message): void
    {
        if ($this->enabledMessages()) {
            $this->response['error'] = true;
            $this->response[$this->messagesKey()][]['warning'] = $message;
        }
    }

    public function responseElement(string $key, $value): static
    {
        $this->response[$key] = $value;
        return $this;
    }

    protected function flash(string $key = 'session_response'): void
    {
        session()->flash($key, $this->fetchResponse());
    }

    public function redirectTo(string $route): static
    {
        $this->redirect_to = $route;
        return $this;
    }

    public function redirectRoute(string $route): static
    {
        $this->redirect_route = $route;
        return $this;
    }

    public function sendResponse(): RedirectResponse
    {
        $this->flash();

        if ($this->redirect_route) {
            return redirect()->route($this->redirect_route);
        }

        if ($this->redirect_to) {
            return redirect()->to($this->redirect_to);
        }

        return redirect()->back()->with('session_response', $this->fetchResponse());
    }

    protected function pushMessages(object $object)
    {
        $messages = $object->fetchResponse()[$this->messagesKey()] ?? [];

        if ($messages) {
            foreach ($messages as $message) {
                $this->response[$this->messagesKey()][] = $message;
            }
            $this->response['error'] = $object->hasErrors();
        }
        return $this;
    }

    public function responseException(Throwable $e, string $message = ''): static
    {
        $this->responseError(!empty($message) ? $message : "Une erreur est survenue.");

        if (auth()->check() && auth()->user()->hasRole('dev')) {
            $this->responseWarning($e->getMessage());
        }
        report($e);
        return $this;
    }

    private function messagesKey(): string
    {
        return ($this->ajax_mode ? 'ajax_' : '') . 'messages';
    }

}
