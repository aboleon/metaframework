<?php

namespace App\Actions\EventManager;

use App\Models\EventManager\Sellable;

class SellablePrice
{

    /**
     * @param \App\Models\EventManager\Sellable $sellable
     */
    public function __construct(public Sellable $sellable)
    {
    }

    public function __invoke(): void
    {
        $this->update();
    }

    public function update(): void
    {
        $this->sellable->prices()->delete();


        if (request()->filled('service_price')) {

            $data = [];
            $input = request('service_price');


            for ($i = 0; $i < count($input['price']); ++$i) {
                $data[] = [
                    'price' => $input['price'][$i],
                    'ends' => $input['ends'][$i],
                ];
            }

            $this->sellable->prices()->createMany($data);

        }
    }
}
