<?php

namespace App\Http\Controllers;


use App\Rules\IsObject;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class RestoreController extends Controller
{
    use ValidationTrait;

    public function process()
    {
        $this->basicValidation();

        try {
            $object = request('object');
            $model = new $object;
            $item = $model->find(request('id'));
            $item->deleted_at = NULL;
            $item->save();
            $this->responseSuccess(__('ui.record_restored'));

        } catch (Throwable $e) {

            $this->responseException($e);

        } finally {

            return $this->sendResponse();
        }

    }

    private function basicValidation()
    {
        $this->validation_rules = [
            'object' => ['required', new IsObject],
            'id' => ['required', 'numeric']
        ];

        $this->validation_messages = [
            'object.required' => __('validation.required', ['attribute' => 'Objet de la requête de restauration']),
            'id.required' => __('validation.required', ['attribute' => "L'id à restaurer"]),
            'id.numeric' => __('validation.required', ['attribute' => "L'id à restaurer n'est pas numérique"]),
        ];
        $this->validation();
    }
}
