<?php

declare(strict_types=1);

namespace MetaFramework\Services\Validation;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;

abstract class ValidationAbstract
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
    }

    public function passedValidation(): void
    {
    }

    public function withValidator(ValidatorContract $validator): void
    {
    }

    /**
     * @return array<int, callable>
     */
    public function after(ValidatorContract $validator): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }

    public function stopOnFirstFailure(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return request()->all();
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * @return array<array<string>>
     */
    public function logic(): array
    {
        return [
            'rules' => $this->rules(),
            'messages' => $this->messages(),
        ];
    }
}
