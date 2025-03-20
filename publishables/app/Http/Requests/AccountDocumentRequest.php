<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountDocumentRequest extends FormRequest
{

    private string $prefix = '';
    private string $dateFormat = 'd/m/Y';

    public function __construct()
    {
        parent::__construct();
        $this->setPrefix('documents');

    }


    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix . '.';
        return $this;
    }

    public function setDateFormat(string $format): static
    {
        $this->dateFormat = $format;
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
            $this->prefix . 'name' => ['required', 'string'],
            $this->prefix . 'serial' => ['required', 'string'],
            $this->prefix . 'emitted_at' => 'date_format:' . $this->dateFormat,
            $this->prefix . 'expires_at' => 'date_format:' . $this->dateFormat . '|after:' . $this->prefix . 'emitted_at',
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        return [
            $this->prefix . 'name.required' => __('front/account.validation.name'),
            $this->prefix . 'serial.required' => __('front/account.validation.serial'),
            $this->prefix . 'name.string' => __('validation.string', ['attribute' => strval(__('ui.title'))]),
            $this->prefix . 'serial.string' => __('validation.string', ['attribute' => strval(__('ui.cards.serial'))]),
            $this->prefix . 'emitted_at' => __('validation.date', ['attribute' => strval(__('ui.emitted_at'))]),
            $this->prefix . 'expires_at.date_format' => __('validation.date', ['attribute' => strval(__('ui.expires_at'))]),
            $this->prefix . 'expires_at.after' => __('validation.after', ['attribute' => strval(__('ui.expires_at')), 'date' => strval(__('ui.emitted_at'))]),
        ];

    }
}
