<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountAddressRequest;
use App\Http\Requests\AccountPhoneRequest;
use App\Models\Account;
use App\Models\AccountAddress;
use App\Models\AccountPhone;
use MetaFramework\Traits\Responses;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AccountPhoneController extends Controller
{
    use Responses;

    /**
     * @var array<string, mixed>
     */
    private array $validated_data = [];

    public function create(Account $account): Renderable
    {
        return view('accounts.phone')->with([
            'route' => route('panel.accounts.phone.store', $account),
            'title' => 'Ajouter un numéro de téléphone pour ' . $account->names(),
            'account' => $account,
            'user_route' => 'panel.accounts',
            'redirect_to' => request()->input('redirect_to'),
            'data' => new AccountPhone(),
        ]);
    }

    public function store(Account $account, AccountPhoneRequest $request): RedirectResponse
    {

        if (!$this->ensureDataIsValid($request)) {
            return $this->sendResponse();
        }
        try {
            $this->manageDefaultPhone($account);
            $account->phones()->save(new AccountPhone($this->validated_data));
            $this->responseSuccess("Le numéro de téléphone est ajouté.");

            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));

        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            return $this->sendResponse();
        }
    }

    public function edit(Account $account, AccountPhone $phone): Renderable
    {
        return view('accounts.phone')->with([
            'account' => $account,
            'data' => $phone,
            'route' => route('panel.accounts.phone.update', [$account, $phone]),
            'method' => 'put',
            'redirect_to' => request()->input('redirect_to'),
            'title' => 'Éditer un numéro de téléphone',
        ]);
    }

    public function update(Account $account, AccountPhone $phone, AccountPhoneRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request)) {
            return $this->sendResponse();
        }

        try {
            $this->manageDefaultPhone($account);
            $phone->update($this->validated_data);
            $this->responseSuccess("Le numéro de téléphone est mis à jour.");
        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));
            return $this->sendResponse();
        }
    }

    /**
     * Ensure we have correctly parsed validated data.
     */
    private function ensureDataIsValid(AccountPhoneRequest $request): bool
    {

        $this->validated_data = is_array($request->validated()) && array_key_exists('phone',$request->validated())
            ? (array)$request->validated('phone')
            : [];

        if (!$this->validated_data) {
            $this->responseWarning("Les données n'ont pas pu être composées correctement");
        }

        $this->validated_data['default'] = isset($this->validated_data['default']) ? 1 : null;

        return (bool)$this->validated_data;

    }

    public function destroy(Account $account, AccountPhone $phone): RedirectResponse
    {
        try {
            $phone->delete();
            $this->responseSuccess("Le numéro de téléphone a été supprimé");
        } catch (Throwable $e)
        {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    private function manageDefaultPhone(Account $account): void
    {
        if (!empty($this->validated_data['default'])) {
            AccountPhone::where('user_id', $account->id)->update(['default' => null]);
        }
    }

}
