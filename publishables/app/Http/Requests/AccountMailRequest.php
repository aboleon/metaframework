<?php

namespace App\Http\Requests;

use App\Models\Account;
use App\Models\AccountMail;
use App\Rules\AccountMailRule;
use Illuminate\Foundation\Http\FormRequest;

class AccountMailRequest extends FormRequest
{

    private string $prefix = '';
    private bool $checkDefault = false;
    private ?int $against_id = null;

    public function __construct(
        public Account      $account,
        public ?AccountMail $instance = null)
    {
        parent::__construct();

        $this->setPrefix('mails');
        if ($this->instance instanceof AccountMail && $this->instance->id) {
            $this->setId($this->instance->id);
        }
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix . '.';
        return $this;
    }

    public function checkDefault(): static
    {
        $this->checkDefault = true;
        return $this;
    }

    public function removePrefix(): static
    {
        $this->prefix = "";
        return $this;
    }


    public function setId(int $id): static
    {
        $this->against_id = $id;
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
     */
    public function rules(): array
    {
        return [
            $this->prefix . 'email' => [
                'required',
                'email',
                'unique:account_mails,email' . ($this->against_id ? ',' . $this->against_id : ''),
                new AccountMailRule($this->account, $this->prefix, $this->checkDefault),
            ]
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        return [
            $this->prefix . 'email.required' => __('validation.required', ['attribute' => strval(__('ui.email_address'))]),
            $this->prefix . 'email.email' => __('validation.email', ['attribute' => strval(__('ui.email_address'))]),
            $this->prefix . 'email.unique' => __('validation.unique', ['attribute' => strval(__('ui.email_address'))]),
        ];

    }
}
