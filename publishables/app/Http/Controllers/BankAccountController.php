<?php

namespace App\Http\Controllers;

use App\Accessors\BankAccounts;
use App\Http\Requests\BankAccountRequest;
use App\Models\BankAccount;
use App\Models\Group;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use MetaFramework\Actions\Suppressor;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class BankAccountController extends Controller
{
    use ValidationTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(): Renderable
    {
        return view('bank.index')->with('data', BankAccount::all()->sortBy('name'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Renderable
    {
        return view('bank.editable')->with([
            'data' => new BankAccount(),
            'label' => '<span class="text-secondary">Créer un compte bancaire </span>',
            'route' => route('panel.bank.store')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BankAccountRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request, 'bank')) {
            return $this->sendResponse();
        }

        try {
            $bank = BankAccount::create($this->validated_data['bank']);
            $this->responseSuccess(__('ui.record_created'));
            $this->redirectTo(route('panel.bank.index'));
            $this->saveAndRedirect(route('panel.bank.index'));
            BankAccounts::resetCache();
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    /**
     * Edit the specified resource.
     */
    public function edit(BankAccount $bank): Renderable
    {
        return view('bank.editable')->with([
            'data' => $bank,
            'label' => '<span class="text-secondary">Éditer un compte bancaire </span>',
            'route' => route('panel.bank.update', $bank)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankAccountRequest $request, BankAccount $bank): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request, 'bank')) {
            return $this->sendResponse();
        }

        try {
            $bank->update($this->validated_data['bank']);
            $this->responseSuccess(__('ui.record_updated'));
            $this->redirect_to = route('panel.bank.index');
            $this->saveAndRedirect(route('panel.bank.index'));
            BankAccounts::resetCache();
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    /**
     * Remove the specified resource from storage.
     * @throws \Exception
     */
    public function destroy(BankAccount $bank): RedirectResponse
    {
        BankAccounts::resetCache();

        return (new Suppressor($bank))
            ->remove()
            ->whitout('object')
            ->responseSuccess(__('Le compte est supprimé.'))
            ->redirectRoute('panel.bank.index')
            ->sendResponse();
    }
}
