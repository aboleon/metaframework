<?php

declare(strict_types=1);

namespace App\Traits;

use MetaFramework\Services\Validation\ValidationTrait;

trait Geo
{
    use ValidationTrait;

    private function geoValidation()
    {
        $this->validation_rules = [
            'wa_geo.text_address' => 'string',
            'wa_geo.postal_code' => 'string',
            'wa_geo.locality' => 'string',
            'wa_geo.street_number' => 'string',
            'wa_geo.route' => 'string',
            'wa_geo.country_code' => 'string',
            'wa_geo.country' => 'string',
        ];

        $this->validation_messages = [
            'wa_geo.text_address.string' => __('validation.string', ['attribute' => __('ui.geo.text_address') ]),
            'wa_geo.postal_code.string' => __('validation.string', ['attribute' => __('ui.geo.postal_code') ]),
            'wa_geo.locality.string' => __('validation.string', ['attribute' => __('ui.geo.locality') ]),
            'wa_geo.street_number.string' => __('validation.string', ['attribute' => __('ui.geo.street_number') ]),
            'wa_geo.route.string' => __('validation.string', ['attribute' => __('ui.geo.route') ]),
            'wa_geo.country_code.string' => __('validation.string', ['attribute' => __('ui.geo.country_code')]),
            'wa_geo.country.string' => __('validation.string', ['attribute' => __('ui.geo.country')]),
        ];

        $this->validation();

    }

}
