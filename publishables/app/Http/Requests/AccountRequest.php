<?php

namespace App\Http\Requests;

use App\Models\Account;
use MetaFramework\Services\Passwords\PasswordValidationSet;
use App\Validation\AccountProfileValidation;
use App\Validation\UserBasicValidation;
use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    /**
     * @var array<array<string>>
     */
    private array $password_validation;

    /**
     * @var array<array<string,mixed>>
     */
    private array $profile_validation;
    /**
     * @var array<array<string,mixed>>
     */
    private array $phone_validation;

    private UserBasicValidation $user_validation;

    public function __construct(public ?Account $account = null)
    {
        parent::__construct();

        $this->user_validation = (new UserBasicValidation)->setPrefix('user');

        if ($this->account instanceof Account && $this->account->id) {
            $this->user_validation->setUserId($this->account->id);
        }

        $this->password_validation = (new PasswordValidationSet(request()))->logic();
        $this->profile_validation = (new AccountProfileValidation($this->account))->logic();
        $this->phone_validation = (new AccountPhoneRequest())->logic();

    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /*
        d(request()->input());
        d(request('phone.phone'));
        d(request('phone.country_code'));
        de($this->phone_validation['rules']);
        */
        return array_merge(
            $this->user_validation->rules(),
            $this->profile_validation['rules'],
            $this->password_validation['rules'],
            $this->phone_validation['rules']
        );

    }

    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        return array_merge(
            $this->user_validation->messages(),
            $this->profile_validation["messages"],
            $this->password_validation['messages'],
            $this->phone_validation['messages']
        );
    }
}
