<?php

namespace App\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GoogleAddressComboValidator
{
    public static function validate($input)
    {
        $rules = [
            'wa_geo' => 'nullable|array',
            'wa_geo.text_address' => 'required|string',
            'wa_geo.locality' => 'required|string',
            'wa_geo.country' => 'required|string',
            'wa_geo.country_code' => 'required|string',
        ];

        $messages = [
            'wa_geo.text_address.required' => 'Le champ adresse est obligatoire.',
            'wa_geo.locality.required' => 'Le champ ville est obligatoire.',
            'wa_geo.country.required' => 'Le champ pays est obligatoire.',
            'wa_geo.country_code.required' => 'Le champ (code) pays est obligatoire.',
        ];

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}