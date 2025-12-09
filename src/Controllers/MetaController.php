<?php

namespace MetaFramework\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MetaFramework\{Accessors\Routing, Models\Forms, Models\Meta, Models\MetaSubModel, Services\Validation\ValidationTrait, Support\Traits\Responses};
use Throwable;

class MetaController extends Controller
{
    use Responses;
    use ValidationTrait;

    private $object;
    private array $data = [];
    private string $backend_url;

    public function __construct()
    {
        $this->backend_url = Routing::backend();
    }

    public function index(?string $type = null): Renderable
    {
        if ($type) {
            $views[] = $this->backend_url . '.mfw.' . $type . '.index';
        }
        $views[] = 'mfw::backend.index';

        return view()->first($views)->with([
            'data' => Meta::where('type','!=','bloc')->when($type, fn($q) => $q->where('type', $type))->orderBy('id', 'desc')->paginate(),
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

            return redirect()->route('mfw.meta.create', ['type' => request('type')]);
        }

        return view('mfw::backend.create_admin');
    }

    public function create($type): Renderable
    {
        $meta = new Meta;
        $meta->type = $type;
        $this->data['data'] = $meta;
        $this->data['model'] = $meta->subModel();

        if (method_exists($this, 'dataView_' . $type)) {
            $this->{'dataView_' . $type}();
        }
        return view()->first([$this->backend_url . '.mfw.' . $type . '.create', 'mfw::backend.create'])->with($this->data);
    }

    public function show($type, int $id = null): Renderable
    {
        $this->data['data'] = Meta::where(function ($q) use ($type, $id) {
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

        $this->data['model'] = (new MetaSubModel($this->data['data']))->model();

        if (method_exists($this, 'dataView_' . $this->data['data']->type)) {
            $this->{'dataView_' . $this->data['data']->type}();
        }

        $views = [];
        $views[] = $this->backend_url . '.mfw.' . $type . '.edit';
        $views[] = 'mfw::backend.edit';

        return view()->first($views)->with($this->data);
    }

    public function edit(Meta $metum): Renderable
    {
        $this->data['data'] = $metum;
        $this->data['model'] = (new MetaSubModel($this->data['data']))->model();

        if (method_exists($this, 'dataView_' . $metum->type)) {
            $this->{'dataView_' . $metum->type}();
        }

        $views = [];
        $views[] = $this->backend_url . '.mfw.' . $metum->type . '.edit';
        $views[] = 'mfw::backend.edit';

        return view()->first($views)->with($this->data);
    }

    public function patch($id): RedirectResponse
    {
        $meta = Meta::withTrashed()->findOrFail($id);
        return $this->update($meta);
    }

    public function store(): RedirectResponse
    {
        $meta = Meta::makeMeta(request('meta_type'));

        $meta->process();
        $meta->processMedia();

        (new MetaSubModel($meta))->process();

        $this->redirect_to = route('mfw.meta.show', ['type' => $meta->type, 'id' => $meta->id]);

        Artisan::call('cache:clear');

        return $this->sendResponse();
    }

    public function update(Meta $metum): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $metum->process();
            $metum->processMedia();
            //$metum->processAttachedForms();

            // Process sub models if any
            (new MetaSubModel($metum))->process();


            $this->responseSuccess(__('mfw.record_created'));
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
                    'name' => request('mfw.forms')
                ]));
            } else {
                if ($this->object->form->name != request('mfw.forms')) {
                    $this->object->form()->update([
                        'name' => request('mfw.forms')
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
