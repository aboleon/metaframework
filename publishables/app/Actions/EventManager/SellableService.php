<?php

namespace App\Actions\EventManager;

use App\Models\EventManager\Sellable\Option;
use App\Models\EventManager\Sellable\Price;
use MetaFramework\Traits\Ajax;
use Throwable;

class SellableService
{
    use Ajax;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
    }

    public function deleteOption(int $id): array
    {
        $this->responseElement('callback', 'ajaxPostDeleteOption');
        try {
            Option::where('id', $id)->delete();
            $this->responseSuccess("L'option a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function deletePrice(int $id): array
    {
        $this->responseElement('callback', 'ajaxPostDeletePrice');
        try {
            Price::where('id', $id)->delete();
            $this->responseSuccess("La ligne de prix a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }
}
