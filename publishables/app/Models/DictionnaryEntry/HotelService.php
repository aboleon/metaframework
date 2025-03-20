<?php

namespace App\Models\DictionnaryEntry;

use App\Interfaces\CustomDictionnaryInterface;
use App\Models\DictionnaryEntry;
use MetaFramework\Polyglote\Traits\Translation;

class HotelService extends DictionnaryEntry implements CustomDictionnaryInterface
{

    use Translation;


    public function translatables(): array
    {
        return [
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
            ],
        ];
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
