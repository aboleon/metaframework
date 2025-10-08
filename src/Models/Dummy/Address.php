<?php

namespace MetaFramework\Models\Dummy;

use MetaFramework\Inputable\Contracts\GooglePlacesInterface;

class Address implements GooglePlacesInterface
{

    public ?string $country_code = null;

}
