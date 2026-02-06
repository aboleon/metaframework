<?php

declare(strict_types=1);

namespace MetaFramework\Services\Validation;

use Illuminate\Foundation\Http\FormRequest;
use MetaFramework\Support\Traits\Responses;

trait ValidationTrait
{
    use Responses;

    protected array $validation_rules = [];

    protected array $validation_messages = [];

    /**
     * @var array<string, mixed>
     */
    protected array $validated_data = [];

    public function addValidationRules(array $rules): void
    {
        $this->validation_rules = array_merge($this->validation_rules, $rules);
    }

    public function addValidationMessages(array $rules): void
    {
        $this->validation_messages = array_merge($this->validation_messages, $rules);
    }

    public function validatedData(?string $key = null): string|array
    {
        return $key ? ($this->validated_data[$key] ?? $this->validated_data) : $this->validated_data;
    }

    public function validatedDataStringable(string $key): string|int|float|null
    {
        return isset($this->validated_data[$key]) && !is_array($this->validated_data[$key]) ? $this->validated_data[$key] : null;
    }

    public function validation(): void
    {
        if ($this->validation_rules) {
            $this->validated_data = request()->validate(
                $this->validation_rules,
                $this->validation_messages
            );
        }
    }

    protected function ensureDataIsValid(FormRequest $request, string $key): bool
    {

        $this->validated_data[$key] = is_array($request->validated()) && array_key_exists($key, $request->validated())
            ? (array) $request->validated($key)
            : [];
        if (!$this->validated_data[$key]) {
            $this->responseWarning(__('mfw.errors.composing_data'));
        }

        return !$this->hasErrors();

    }
}
