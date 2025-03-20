<?php

namespace App\Http\Controllers;

use App\Models\Forms;
use App\Models\Meta;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class MetaController extends Controller
{
    use ValidationTrait;

    private $object;
    private $data = [];

    public function index($type): Renderable
    {
        return view()->first([
            'panel.' . $type . '.index',
            'panel.list.' . $type,
            'panel.list.default'
        ])->with([
            'data' => Meta::where('type', $type)->orderBy('id', 'desc')->paginate(),
            'type' => $type,
            'locale' => app()->getLocale()
        ]);
    }

    public function createAdmin(): Renderable|RedirectResponse
    {
        if (request()->isMethod('post')) {
            $this->validation_rules = [
                'type' => 'required'
            ];
            $this->validation_messages = [
                'type.required' => __('validation.required', ['attribute' => 'Le type'])
            ];

            $this->validation();

            return redirect()->route('panel.meta.create', ['type' => request('type')]);
        }

        return view('meta.create_admin');
    }

    public function create($type): Renderable
    {
        $meta = new Meta;
        $meta->type = $type;
        $this->data['data'] = $meta;
        $this->data['model'] = $meta->model()->instance;

        if (method_exists($this, 'dataView_' . $type)) {
            $this->{'dataView_' . $type}();
        }

        return view()->first(['panel.' . $type . '.create', 'meta.create'])->with($this->data);
    }


    public function store(): RedirectResponse
    {
        $meta = Meta::makeMeta(request('meta_type'));
        $meta->process();

        if (method_exists($meta->model()->instance, 'storeModel')) {
            $meta->model()->instance->storeModel();
        }

        $this->redirect_to = route('panel.meta.show', ['type' => $meta->type, 'id' => $meta->id]);

        Artisan::call('cache:clear');

        return $this->sendResponse();
    }

    public function show($type, int $id = null): Renderable
    {
        $this->data['data'] = Meta::withTrashed()
            ->where(function ($q) use ($type, $id) {
                $q->where('type', $type);
                if ($id) {
                    $q->where('id', $id);
                }
            if (request()->filled('taxonomy')) {
                $q->where('taxonomy', request('taxonomy'));
            }
            })->first();


        if (!$this->data['data']) {
            abort(404, 'Ce type de contenu n\'est pas défini.');
        }

        $this->data['model'] = $this->data['data']->model()->instance;

        if (method_exists($this, 'dataView_' . $this->data['data']->type)) {
            $this->{'dataView_' . $this->data['data']->type}();
        }

        $views = [];
        $views[] = 'panel.show.' . $type;
        $views[] = 'panel.show.default';

        return view()->first($views)->with($this->data);
    }

    public function edit(Meta $metum)
    {
        return view()->first(['meta.create_' . $metum->type, 'meta.create'])->with('data', $metum);
    }

    public function patch($id): RedirectResponse
    {
        $meta = Meta::withTrashed()->findOrFail($id);
        return $this->update($meta);
    }

    public function update(Meta $metum): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $metum->process();
            $metum->processMedia();
            //$metum->processAttachedForms();

            if (method_exists($metum->model()->instance, 'processModel')) {
                $metum->model()->instance->unsetMetaAsTransltable();
                $metum->model()->instance->processModel();
            }

            $this->responseSuccess(__('ui.record_created'));
            Artisan::call('cache:clear');

        } catch (Throwable $e) {
            $this->responseException($e);
        }
        DB::commit();

        return $this->sendResponse();
    }


    public function destroy(Meta $metum): RedirectResponse
    {
        $type = $metum->type;

        $metum->delete();
        $this->responseSuccess("La suppression est effectuée");

        if (!in_array($type, ['bloc'])) {
            Artisan::call('cache:clear');
        }
        // TODO:: delete media
        if (request()->filled('redirect')) {
            $this->redirectTo(request('redirect'));
        }
        return $this->sendResponse();
    }

    private function processAttachedForms()
    {
        if (request()->filled('meta.forms')) {
            if (is_null($this->object->form)) {
                $this->object->form()->save(new Forms([
                    'name' => request('meta.forms')
                ]));
            } else {
                if ($this->object->form->name != request('meta.forms')) {
                    $this->object->form()->update([
                        'name' => request('meta.forms')
                    ]);
                }
            }
        } else {
            if (!is_null($this->object->form)) {
                $this->object->form->delete();
            }
        }
    }

}
