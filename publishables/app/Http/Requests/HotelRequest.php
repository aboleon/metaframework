<?php

namespace App\Http\Requests;

use App\Enum\Stars;
use App\Traits\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use MetaFramework\Services\Validation\GoogleAddressValidation;

class HotelRequest extends FormRequest
{
    use Locale;

    private string $prefix = '';

    /**
     * @var array<array<string,mixed>>
     */
    private array $address_validation;

    public function __construct()
    {
        parent::__construct();
        $this->setPrefix('hotel');
        $this->address_validation = (new GoogleAddressValidation())->setPrefix('wa_geo')->logic();
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix . '.';
        return $this;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
        return array_merge([
            $this->prefix . 'name' => 'required|string|max:255',
            $this->prefix . 'stars' => 'nullable|integer',
            $this->prefix . 'services' => 'nullable|array',
            $this->prefix . 'services.*' => 'integer',
            $this->prefix . 'first_name' => 'nullable|string|max:255',
            $this->prefix . 'last_name' => 'nullable|string|max:255',
            $this->prefix . 'email' => 'nullable|email|max:255',
            $this->prefix . 'phone' => 'nullable|numeric',
            $this->prefix . 'website' => 'nullable|string',
            $this->prefix . 'description.*' => 'nullable|string'
        ],
            $this->address_validation['rules']
        );
    }



    public function messages(): array
    {
        return array_merge(
            [
                $this->prefix . 'name.required' => __('validation.required', ['attribute' => "Nom de l'hôtel"]),
                $this->prefix . 'name.string' => __('validation.string', ['attribute' => strval(__('mfw.name'))]),
                $this->prefix . 'first_name.string' => __('validation.string', ['attribute' => __('mfw.first_name')]),
                $this->prefix . 'last_name.string' => __('validation.string', ['attribute' => "Le nom du contact commercial"]),
                $this->prefix . 'email.email' => __('validation.required', ['attribute' => strval(__('ui.email_address'))]),
                $this->prefix . 'phone.numeric' => __('validation.phone', ['attribute' => strval(__('account.phone'))]),
                $this->prefix . 'website.string' => __('validation.website', ['attribute' => "Le site Internet"]),
                $this->prefix . 'description.*.string' => __('validation.string', ['attribute' => __('mfw.description')]),

            ],
            $this->address_validation['messages']
        );
    }
}
