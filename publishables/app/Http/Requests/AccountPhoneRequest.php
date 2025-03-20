<?php

namespace App\Http\Requests;

use MetaFramework\Services\Validation\ValidationAbstract;

class AccountPhoneRequest extends ValidationAbstract
{

    private string $prefix = '';

    public function __construct()
    {
        $this->setPrefix('phone');
    }


    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix . '.';
        return $this;
    }

    public function removePrefix(): static
    {
        $this->prefix = "";
        return $this;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            $this->prefix . 'name' => ['nullable', 'string'],
            $this->prefix . 'default' => ['nullable', 'boolean'],
            $this->prefix . 'country_code' => ['nullable', 'required_with:' . $this->prefix . 'phone'],
            $this->prefix . 'phone' => ['nullable', 'phone:' . $this->prefix . 'country_code'],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        return [
            $this->prefix . 'name.string' => __('validation.string', ['attribute' => strval(__('ui.title'))]),
            $this->prefix . 'default.boolean' => __('validation.boolean', ['attribute' => "Le choix de définir ce numéro comme principal"]),
            $this->prefix . 'phone.required' => __('validation.required', ['attribute' => __('account.phone')]),
            $this->prefix . 'phone.phone' => __('validation.phone', ['attribute' => strval(__('account.phone'))]),
            $this->prefix . 'country_code.required_with' => __('validation.required_with', [
                'attribute' => __('Country code'),
                'values' => __('Phone')
            ]),
        ];

    }
}
