<?php

namespace App\Rules;

use App\Models\Account;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AccountMailRule implements ValidationRule
{
    public function __construct(
        public Account $account,
        public string  $prefix = '',
        public bool    $checkDefault = false
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value == $this->account->email) {
            $fail('Cette adresse est déjà l\'adresse principale du compte.');
        }
        if (
            (
                $this->checkDefault ||
                request()->has($this->prefix . 'default')
            )
            &&
            User::where('email', $value)->where('id', '!=', $this->account->id)->exists()
        ) {
            $fail('Cette adresse ne peut pas être définie comme principale car elle est affectée à un autre compte.');
        }
    }
}
