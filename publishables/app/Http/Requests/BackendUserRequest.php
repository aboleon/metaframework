<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Validation\BackendUserProfileValidation;
use MetaFramework\Services\Passwords\PasswordValidationSet;
use App\Validation\UserBasicValidation;
use Illuminate\Foundation\Http\FormRequest;

class BackendUserRequest extends FormRequest
{
    /**
     * @var array<array<string>>
     */
    private array $password_validation;

    /**
     * @var array<array<string,mixed>>
     */
    private array $profile_validation;

    private UserBasicValidation $user_validation;

    public function __construct(public ?User $user = null)
    {
        parent::__construct();

        $this->user_validation = (new UserBasicValidation)->setPrefix('user');

        if ($this->user instanceof User && $this->user->id) {
            $this->user_validation->setUserId($this->user->id);
        }

        $this->password_validation = (new PasswordValidationSet(request()))->logic();
        $this->profile_validation = (new BackendUserProfileValidation())->logic();

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
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = array_merge(
            $this->user_validation->rules(),
            $this->password_validation['rules'],
        );
        if (request()->has('has_account_profile')) {
            $rules = array_merge(
                $rules,
                $this->profile_validation['rules'],
            );
        }
        return $rules;

    }

    /**
     * @return array<string,mixed>
     */
    public function messages(): array
    {
        $messages = array_merge(
            $this->user_validation->messages(),
            $this->password_validation["messages"]
        );

        if (request()->has('has_account_profile')) {
            $messages = array_merge(
                $messages,
                $this->profile_validation['messages']
            );
        }

        return $messages;

    }
}
