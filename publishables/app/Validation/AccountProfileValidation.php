<?php

namespace App\Validation;

use MetaFramework\Services\Validation\ValidationAbstract;
use App\Accessors\Dictionnaries;
use App\Enum\Civility;
use App\Models\Account;
use Illuminate\Validation\Rules\Enum;

class AccountProfileValidation extends ValidationAbstract
{
    public function __construct(public ?Account $account = null)
    {

    }

    /**
     * @return array<string, array<int, \Illuminate\Validation\Rules\Enum|string>|string>>
     */
    public function rules(): array
    {
        return [
            'profile.passport_first_name' => 'nullable|string|max:255',
            'profile.passport_last_name' => 'nullable|string|max:255',
            'profile.function' => 'nullable|string|max:255',
            'profile.birth' => 'nullable|date_format:d/m/Y',
            'profile.civ' => [
                'nullable',
                new Enum(Civility::class),
            ],
            'profile.default_billing_address' => 'nullable|integer',
            'profile.blacklisted' => 'boolean',
            'profile.blacklist_comment' => 'nullable|string',
            'profile.notes' => 'nullable',
            'profile.domain_id' => 'required',
            'profile.account_type' => 'required',
            'profile.title_id' => 'nullable|integer',
            'profile.base_id' => 'required',
            'profile.profession_id' => 'required',
            'profile.language_id' => 'nullable|integer',
            'profile.savant_society_id' => 'nullable|integer',
            'profile.cotisation_year' => 'nullable|integer',
            'profile.company_name' => 'nullable|string',
            'profile.establishment_id' => ['nullable', 'exists:establishments,id'],
            'profile.rpps' => 'nullable|string'
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'profile.birth.date' => __('validation.date', ['attribute' => strval(__('forms.fields.birth'))]),
            'profile.domain_id.required' => __('validation.required', ['attribute' => Dictionnaries::title('domain')]),
            'profile.base_id.required' => __('validation.required', ['attribute' => Dictionnaries::title('base')]),
            'profile.title_id.required' => __('validation.required', ['attribute' => Dictionnaries::title('titles')]),
            'profile.profession_id.required' => __('validation.required', ['attribute' => Dictionnaries::title('professions')]),
            'profile.account_type.required' => __('validation.required', ['attribute' => "Type de client"]),
            'profile.establishment_id.exists' => __('validation.exists', ['attribute' => ucfirst(trans_choice('divine.establishment', 1))]),
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
