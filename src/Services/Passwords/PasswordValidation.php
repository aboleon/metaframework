<?php

declare(strict_types=1);

namespace MetaFramework\Services\Passwords;

use MetaFramework\Services\Validation\ValidationAbstract;

final class PasswordValidation extends ValidationAbstract
{
    private int $password_length = 8;

    /**
     * use MetaFramework\Actions\Fortify\PasswordValidationRules;
     * $this->passwordRules(),
     *
     * @return array<string>
     */
    public function rules(): array
    {
        $confirm = request()->has('password_confirmation')
            ? 'confirmed'
            : '';

        return [
            'password' => $confirm . '|alpha_dash|min:' . $this->password_length
        ];
    }

    /**
     * @return array<string>
     */
    public function messages(): array
    {
        return [
            'password.confirmed' => 'Les mots de passe ne sont pas identiques',
            'password.alpha_dash' => 'Les mots de passe peuvent contenir chiffre, lettres et tirets',
            'password.min' => 'Le mot de passe doit être au minimum ' . $this->password_length . ' caractères',
        ];
    }
}
