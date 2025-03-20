<?php

namespace App\Actions\EventManager;

use App\Models\EventManager\Sellable;

class SellableOption
{
    public function __construct(
        public Sellable $sellable
    )
    {
    }

    public function __invoke(): void
    {
        $this->update();
    }

    public function update(): void
    {
        $this->sellable->options()->delete();

        $data = (array)request('service_option');
        if ($data) {
            $this->sellable->options()->createMany($data);
        }
    }
}
