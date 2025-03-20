<?php

namespace App\Traits\DataTables;

use Illuminate\Http\Request;
use MetaFramework\Traits\Responses;
use ReflectionClass;
use Throwable;

trait MassDelete
{
    use Responses;

    public function massDelete(Request $request, string $name = 'name'): array
    {
        $modelPath = $request->get('model_path', $request->get('model'));
        $deletedMessage = $request->get('deleted_message');

        $this->enableAjaxMode();

        if (!$request->filled('ids')) {
            $this->responseWarning("Aucun identifiant n'a été fourni pour suppression.");
            return $this->fetchResponse();
        }

        try {
            $model = (new ReflectionClass('\App\Models\\' . $modelPath))->newInstance();

        } catch (Throwable $e) {
            $this->responseException($e, $request->get('model') . " n'est pas une class valide.");
            return $this->fetchResponse();
        }

        $ids = explode(',', $request->get('ids'));

        $items = $model->query()->whereIn('id', $ids)->get()->pluck($name, 'id')->toArray();
        foreach ($ids as $item) {
            try {
                $model->query()->where('id', $item)->delete();
                $this->responseSuccess($deletedMessage ?? (($items[$item] ?? "L'élément") . ' a été supprimé'));
            } catch (Throwable $e) {

                $this->responseException($e, 'Une erreur est survenue sur la tentative de suppression de ' . ($items[$item] ?? 'l\'id' . $item) . '. Ligne non supprimée.');
            }
        }
        return $this->fetchResponse();
    }
}
