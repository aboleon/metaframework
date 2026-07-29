<?php

declare(strict_types=1);

namespace MetaFramework\Services\Validation;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
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
        return isset($this->validated_data[$key]) && ! is_array($this->validated_data[$key]) ? $this->validated_data[$key] : null;
    }

    public function validation(ValidationAbstract|FormRequest|string|null $validation = null): void
    {
        if ($validation instanceof FormRequest || (is_string($validation) && is_subclass_of($validation, FormRequest::class))) {
            $this->validated_data = $this->validationFromFormRequest($validation);

            return;
        }

        if ($validation instanceof ValidationAbstract || (is_string($validation) && is_subclass_of($validation, ValidationAbstract::class))) {
            $this->validated_data = $this->validationFromDefinition($validation);

            return;
        }

        if ($this->validation_rules) {
            $this->validated_data = request()->validate(
                $this->validation_rules,
                $this->validation_messages
            );
        }
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    protected function validationFromDefinition(ValidationAbstract|string $validation): array
    {
        $validation = $this->resolveValidationDefinition($validation);

        if (! $validation->authorize()) {
            throw new AuthorizationException;
        }

        $validation->prepareForValidation();

        $rules = array_merge($this->validation_rules, $validation->rules());
        $messages = array_merge($this->validation_messages, $validation->messages());

        if (! $rules) {
            return [];
        }

        $validator = Validator::make(
            $validation->validationData(),
            $rules,
            $messages,
            $validation->attributes()
        )->stopOnFirstFailure($validation->stopOnFirstFailure());

        $validation->withValidator($validator);

        $afterCallbacks = $validation->after($validator);
        if ($afterCallbacks) {
            $validator->after($afterCallbacks);
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validation->passedValidation();

        return $validator->validated();
    }

    /**
     * @throws ValidationException
     */
    protected function validationFromFormRequest(FormRequest|string $validation): array
    {
        $formRequest = $this->resolveFormRequest($validation);

        $formRequest->validateResolved();

        $validated = $formRequest->validated();

        return is_array($validated) ? $validated : [];
    }

    protected function resolveValidationDefinition(ValidationAbstract|string $validation): ValidationAbstract
    {
        if (is_string($validation)) {
            $validation = app($validation);
        }

        if (! $validation instanceof ValidationAbstract) {
            throw new InvalidArgumentException('Validation definition must extend ValidationAbstract.');
        }

        return $validation;
    }

    protected function resolveFormRequest(FormRequest|string $validation): FormRequest
    {
        if (is_string($validation)) {
            $validation = app($validation);
        }

        if (! $validation instanceof FormRequest) {
            throw new InvalidArgumentException('Validation request must extend FormRequest.');
        }

        $formRequest = FormRequest::createFrom(request(), $validation);
        $formRequest->setContainer(app());
        $formRequest->setRedirector(app('redirect'));

        return $formRequest;
    }

    protected function ensureDataIsValid(FormRequest $request, string $key): bool
    {

        $this->validated_data[$key] = is_array($request->validated()) && array_key_exists($key, $request->validated())
            ? (array) $request->validated($key)
            : [];
        if (! $this->validated_data[$key]) {
            $this->responseWarning(__('mfw::mfw.errors.composing_data'));
        }

        return ! $this->hasErrors();

    }
}
