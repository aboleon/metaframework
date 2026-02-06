<?php

declare(strict_types=1);

namespace MetaFramework\Services\Validation;

abstract class ValidationAbstract
{
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
