<?php

namespace App\Traits;

use App\Models\Vat;
use Illuminate\Support\Facades\Cache;

trait Price
{
    public function readableAmount(?int $price): string
    {
        return $price ? number_format($price, 0, ' ') : 0;
    }
}
