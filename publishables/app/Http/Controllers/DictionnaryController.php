<?php

namespace App\Http\Controllers;

use App\Accessors\Dictionnaries;
use App\DataTables\DictionnaryDataTable;
use App\Helpers\AuthHelper;
use App\Models\Dictionnary;
use App\Traits\DataTables\MassDelete;
use App\Traits\Locale;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use MetaFramework\Actions\Translator;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class DictionnaryController extends Controller
{
    use MassDelete;
    use Locale;
    use ValidationTrait;

    public function index(DictionnaryDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('dictionnary.index');
    }

    public function create(): Renderable|RedirectResponse
    {
        if (!auth()->user()->hasRole('dev')) {
            $this->responseWarning("Vous ne pouvez pas créer de nouveau dictionnaires.");
            return $this->sendResponse();
        }

        return view('dictionnary.create')->with([
            'data' => new Dictionnary,
            'route_index' => $this->routeIndex(),
            'label' => "Ajouter un dictionnaire",
            'route' => route('panel.dictionnary.store')
        ]);
    }

    /**
     * @throws \Exception
     */
    public function store(): RedirectResponse
    {
        $this->basicValidation();

        $dictionnary = new Dictionnary;
        $dictionnary->type = request('type');
        $dictionnary->slug = Str::snake(request('slug'));

        return (new Translator($dictionnary))
            ->update()
            ->responseSuccess(__('ui.record_created'))
            ->redirectRoute('panel.dictionnary.index')
            ->sendResponse();
    }

    /**
     * @throws \Exception
     */
    public function update(Dictionnary $dictionnary): RedirectResponse
    {
        $this->basicValidation();

        Dictionnaries::reset($dictionnary->slug);

        $dictionnary->type = request('type');
        $dictionnary->slug = Str::snake(request('slug'));

        return (new Translator($dictionnary))
            ->update()
            ->responseSuccess(__('ui.record_updated'))
            ->redirectRoute('panel.dictionnary.index')
            ->sendResponse();
    }

    public function edit(Dictionnary $dictionnary): Renderable
    {
        return view('dictionnary.create')->with([
            'data' => $dictionnary,
            'label' => "<span class='text-secondary'>Éditer un dictionnaire</span>",
            'route' => route('panel.dictionnary.update', $dictionnary->id),
            'route_index' => route('panel.dictionnary.index')
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Dictionnary $dictionnary): RedirectResponse
    {
        if (!AuthHelper::isDev()) {
            $this->responseWarning("Vous ne pouvez pas supprimer un dictionnaire.");
            return $this->sendResponse();
        }

        try {
            Dictionnaries::reset($dictionnary->slug);
            $dictionnary->delete();

            $this->redirectRoute('panel.dictionnary.index')
                ->responseSuccess(__('ui.record_deleted'));
        } catch (Throwable $e) {
            $this->responseException($e, "Ce dictionnaire ne peut pas être supprimé car des entrées lui appartenant sont reliées à des contacts.");
        }

        return $this->sendResponse();
    }

    public function basicValidation(): void
    {
        $this->validation_rules = [
            'name.' . $this->defaultLocale() => 'required'
        ];

        $this->validation_messages = [
            'name.' . $this->defaultLocale() . '.required' => __('validation.required', ['attribute' => __('ui.title')])
        ];

        $this->validation();
    }

    private function routeIndex(): string
    {
        return route('panel.dictionnary.index');
    }
}
