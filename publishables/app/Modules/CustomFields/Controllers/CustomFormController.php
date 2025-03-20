<?php

namespace App\Modules\CustomFields\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\CustomFields\Models\CustomFieldForm;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFramework\Traits\Responses;
use Throwable;

class CustomFormController extends Controller
{
    use Responses;

    public function edit(CustomFieldForm $customfield): Renderable
    {
        return view('custom_fields.edit')->with([
            'data' => $customfield,
            'route' => route('panel.customfields.update', $customfield),
            'title' => "Édition de champs sur mesure"
        ]);
    }


    public function create(): Renderable
    {
        return view('custom_fields.edit')->with([
            'data' => new CustomFieldForm(),
            'route' => route('panel.customfields.store'),
            'title' => "Création de champs sur mesure",
            'model' => request('model'),
            'model_id' => request('id')
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function store(): RedirectResponse
    {
        try {
            $customform = new CustomFieldForm();
            $customform->model_type = request(('model_type'));
            $customform->model_id = (int)request(('model_id'));
            $customform->save();

            if (request('key')) {
                foreach (request('key') as $key) {
                    $module = $customform->modules()->updateOrCreate(
                        [
                            'key' => $key
                        ],
                        [
                            'title' => request($key . '.title'),
                            'required' => Arr::has(request($key), 'required'),
                            'type' => request($key . '.type') ?? 'text',
                            'subtype' => request($key . '.subtype') ?? 'text',
                            'position' => request($key . '.position')
                        ]
                    );

                    $lines = Arr::has(request($key), 'line') ? array_keys(request($key . '.line')) : [];

                    $module->data()->whereNotIn('key', $lines)->delete();

                    if ($lines) {
                        foreach ($lines as $line) {
                            $module->data()->updateOrCreate(
                                ['key' => $line],
                                ['content' => request($key . '.line.' . $line)]
                            );
                        }
                    }
                }

            }
            $this->redirectTo(route('panel.customfields.edit', $customform));

            $this->responseSuccess("Les champs ont été enregistrés");
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(CustomFieldForm $customfield): RedirectResponse
    {
        try {

            if((array)request('key')) {
                $customfield->modules()->whereNotIn('key', request('key'))->delete();
            } else {
                $customfield->modules()->delete();
            }
            if (request('key')) {
                foreach (request('key') as $key) {
                    $module = $customfield->modules()->updateOrCreate(
                        [
                            'key' => $key
                        ],
                        [
                            'title' => request($key . '.title') ?? 'NC',
                            'required' => Arr::has(request($key), 'required'),
                            'type' => request($key . '.type') ?? 'text',
                            'subtype' => request($key . '.subtype'),
                            'position' => request($key . '.position')
                        ]
                    );

                    $lines = Arr::has(request($key), 'line') ? array_keys(request($key . '.line')) : [];

                    $module->data()->whereNotIn('key', $lines)->delete();

                    if ($lines) {
                        foreach ($lines as $line) {
                            $module->data()->updateOrCreate(
                                [
                                    'key' => $line
                                ],
                                [
                                    'content' => request($key . '.line.' . $line)
                                ]
                            );
                        }
                    }
                }

            }

            $customfield->touch();

            $this->responseSuccess("Le formulaire a été mis à jour");
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }
}
