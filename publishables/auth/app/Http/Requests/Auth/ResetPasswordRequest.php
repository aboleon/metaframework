<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => __('mfw-auth.password.forgotten.token'),
            'email.required' => __('mfw-auth.password.forgotten.validation.email_required'),
            'email.email' => __('mfw-auth.password.forgotten.validation.email_invalid'),
            'password.required' => __('mfw-auth.password.forgotten.validation.password_required'),
            'password.confirmed' => __('mfw-auth.password.are_not_identical'),
            'password_confirmation.required' => __('mfw-auth.password.forgotten.validation.password_confirmation_required'),
        ];
    }
}
