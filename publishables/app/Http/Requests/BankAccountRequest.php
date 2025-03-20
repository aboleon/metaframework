<?php

namespace App\Http\Requests;

use App\Models\BankAccount;
use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
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
        $rules = [];
        foreach ((new BankAccount())->getFillable() as $item) {
            $rules['bank.' . $item] = 'required';
        }
        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        $messages = [];
        foreach ((new BankAccount())->getFillable() as $item) {
            $messages['bank.' . $item.'.required'] = __('validation.required', ['attribute' => strval(__('bank.'.$item))]);
        }
        return $messages;
    }
}
