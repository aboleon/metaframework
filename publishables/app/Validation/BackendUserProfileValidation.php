<?php

namespace App\Validation;

use MetaFramework\Services\Validation\ValidationAbstract;

class BackendUserProfileValidation extends ValidationAbstract
{
    /**
     * @return array<string>>
     */
    public function rules() : array
    {
        return [
            'profile.job' => 'required',
            'profile.mobile' => 'phone:INTERNATIONAL,FR',
            'profile.phone' => 'phone:INTERNATIONAL,FR',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'profile.job.required' => __('validation.required', ['attribute' => 'La fonction']),
            'profile.mobile.phone' => __('validation.phone', ['attribute' => strval(__('forms.fields.mobile_phone'))]),
            'profile.phone.phone' => __('validation.phone', ['attribute' => strval(__('forms.fields.direct_line'))]),
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
