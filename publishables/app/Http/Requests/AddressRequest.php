<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'nullable|string|max:255',
            'address1' => 'required|string|max:255',
            'address2' => 'nullable|string|max:255',
            'postal_code' => 'required|numeric',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
            'lat' => 'nullable|numeric',
            'lon' => 'nullable|numeric',
        ];
    }
}
