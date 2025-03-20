<?php

namespace App\Actions;

use App\Http\Requests\SellableRequest;
use App\Models\SellableByEvent;
use MetaFramework\Services\Validation\ValidationInstance;
use MetaFramework\Traits\Ajax;
use Throwable;

class SellableActions
{
    use Ajax;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
    }

    public function attachToEvent(int $event_id, int $sellable_id): array
    {
        $request = new SellableRequest();

        $validation = new ValidationInstance();
        $validation->addValidationRules($request->rules());
        $validation->addValidationMessages($request->messages());
        $validation->validation();


        try {
            SellableByEvent::updateOrCreate(
                ['event_id' => $event_id, 'sellable_id' => $sellable_id],
                $validation->validatedData()
            );

            $this->responseSuccess("La configuration a été enregistrée.");

        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function removeCustomization(int $event_id, int $sellable_id): array
    {

        try {
            SellableByEvent::where(['event_id' => $event_id, 'sellable_id' => $sellable_id])->delete();
            $this->responseSuccess("La configuration a été supprimée.");

        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }
}
