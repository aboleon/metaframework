<?php

namespace App\Http\Controllers;

use App\DataTables\SellableDataTable;
use App\Http\Requests\SellableRequest;
use App\Models\Account;
use App\Models\Sellable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use MetaFramework\Actions\Suppressor;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class SellableController extends Controller
{
    use ValidationTrait;

    public function index(SellableDataTable $dataTable): JsonResponse|View
    {
        return $dataTable->render('sellable.index', [
            'archived' => request()->routeIs('panel.sellables.archived')
        ]);
    }

    public function create(): Renderable
    {
        $data = [
            'data' => new Sellable,
            'route' => route('panel.sellables.store'),
        ];

        return view('sellable.edit')->with($data);
    }

    public function store(SellableRequest $request): RedirectResponse
    {
        try {

            $sellable = Sellable::create($request->validated());

            $this->responseSuccess(__('ui.record_created'));
            $this->redirectTo(route('panel.sellables.index'));
            $this->saveAndRedirect(route('panel.sellables.index'));

        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    public function edit(int $id): Renderable
    {

        $sellable = Sellable::withTrashed()->findOrFail($id);

        $data = [
            'data' => $sellable,
            'route' => route('panel.sellables.update', $sellable),
        ];

        return view('sellable.edit')->with($data);
    }

    public function update(Sellable $sellable, SellableRequest $request): RedirectResponse
    {
        try {
            $sellable->update($request->validated());
            $this->redirect_to = route('panel.sellables.index');
            $this->saveAndRedirect(route('panel.sellables.index'));
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    /**
     * @throws \Exception
     */
    public function destroy(Sellable $sellable): RedirectResponse
    {
        return (new Suppressor($sellable))
            ->remove()
            ->responseSuccess(__('ui.record_deleted'))
            ->redirectRoute('panel.sellables.index')
            ->whitout('object')
            ->sendResponse();
    }
    public function restore(int $id): RedirectResponse
    {
        try {
            $sellable = Sellable::withTrashed()->findOrFail($id);
            $sellable->restore();
            $this->responseSuccess("L'article a été réactivé");

        } catch (Throwable) {

            $this->responseSuccess("Cet article n'existe pas");
        }

        return $this->sendResponse();
    }
}
