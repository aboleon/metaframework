<?php

namespace App\Models\DictionnaryEntry;

use App\Interfaces\CustomDictionnaryInterface;
use MetaFramework\Polyglote\Traits\Translation;

class Sessions implements CustomDictionnaryInterface
{

    use Translation;


    public function translatables(): array
    {
        return [];
    }

    public function customData(): array
    {
        return [];
    }

    public function mediaSettings(): array
    {
        $this->fillables = [
            '_media' =>
                [
                    'label' => 'Image / Picto',
                ]
        ];

        return $this->fillables;
    }
}
