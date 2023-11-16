<?php

namespace MetaFramework\Services\Validation;


class GoogleAddressValidation extends ValidationAbstract
{

    private string $prefix = '';

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix . '.';
        return $this;
    }

    /**
     * @return array<string, array<int, \Illuminate\Validation\Rules\Enum|string>|string>>
     */
    /**
     * @return array<string, array<int,string>>
     */
    public function rules(): array
    {
        return [
            $this->prefix . 'street_number' => ['nullable', 'string'],
            $this->prefix . 'route' => ['nullable', 'string'],
            $this->prefix . 'locality' => ['nullable', 'string'],
            $this->prefix . 'postal_code' => ['nullable', 'string'],
            $this->prefix . 'country_code' => ['nullable', 'string'],
            $this->prefix . 'administrative_area_level_1' => 'nullable',
            $this->prefix . 'administrative_area_level_1_short' => 'nullable',
            $this->prefix . 'administrative_area_level_2' => 'nullable',
            $this->prefix . 'text_address' => 'required',
            $this->prefix . 'lat' => ['nullable', 'numeric'],
            $this->prefix . 'lon' => ['nullable', 'numeric'],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        return [
            $this->prefix . 'street_number.string' => __('validation.string', ['attribute' => strval(__('mfw.geo.street_number'))]),
            $this->prefix . 'route.string' => __('validation.string', ['attribute' => strval(__('mfw.geo.route'))]),
            $this->prefix . 'locality.string' => __('validation.string', ['attribute' => strval(__('mfw.geo.locality'))]),
            $this->prefix . 'postal_code.string' => __('validation.string', ['attribute' => strval(__('mfw.geo.postal_code'))]),
            $this->prefix . 'country_code.string' => __('validation.string', ['attribute' => strval(__('mfw.geo.country_code'))]),
            $this->prefix . 'text_address.required' => __('validation.required', ['attribute' => strval(__('mfw.geo.text_address'))]),
            $this->prefix . 'lat.numeric' => __('validation.required', ['attribute' => strval(__('mfw.geo.lat'))]),
            $this->prefix . 'lon.numeric' => __('validation.required', ['attribute' => strval(__('mfw.geo.lat'))]),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function logic(): array
    {
        return [
            'rules' => $this->rules(),
            'messages' => $this->messages(),
        ];
    }
}
