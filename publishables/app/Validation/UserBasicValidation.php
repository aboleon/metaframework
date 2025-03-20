<?php

namespace App\Validation;


use Illuminate\Validation\Rule;
use MetaFramework\Services\Validation\ValidationAbstract;

class UserBasicValidation extends ValidationAbstract
{

    private string $prefix = '';
    private ?int $against_user_id = null;

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix . '.';
        return $this;
    }

    public function setUserId(int $user_id): static
    {
        $this->against_user_id = $user_id;
        return $this;
    }

    /**
     * @return array<string, array<int,string>>
     */
    public function rules(): array
    {
        $emailRule = Rule::unique('users', 'email')
            ->whereNull('deleted_at');

        if ($this->against_user_id) {
            $emailRule->ignore($this->against_user_id);
        }

        return [
            $this->prefix . 'first_name' => ['required', 'string', 'max:255'],
            $this->prefix . 'last_name' => ['required', 'string', 'max:255'],
            $this->prefix . 'email' => ['required', 'email', $emailRule],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function messages(): array
    {
        return [
            $this->prefix . 'first_name.required' => __('validation.required', ['attribute' => strval(__('account.first_name'))]),
            $this->prefix . 'first_name.string' => __('validation.string', ['attribute' => strval(__('account.first_name'))]),
            $this->prefix . 'last_name.required' => __('validation.required', ['attribute' => strval(__('account.last_name'))]),
            $this->prefix . 'last_name.string' => __('validation.string', ['attribute' => strval(__('account.first_name'))]),
            $this->prefix . 'email.required' => __('validation.required', ['attribute' => strval(__('ui.email_address'))]),
            $this->prefix . 'email.email' => __('validation.email', ['attribute' => strval(__('ui.email_address'))]),
            $this->prefix . 'email.unique' => __('validation.unique', ['attribute' => strval(__('ui.email_address'))]),
        ];
    }
}
