<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountAddressRequest;
use App\Models\Account;
use App\Models\AccountAddress;
use App\Traits\ValidationAddress;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AccountAddressController extends Controller
{
    use ValidationAddress;

    public function create(Account $account): Renderable
    {
        return view('address.account')->with([
            'route' => route('panel.accounts.addresses.store', $account),
            'title' => 'Ajouter une addresse pour ' . $account->names(),
            'account' => $account,
            'user_route' => 'panel.accounts',
            'redirect_to' => request()->input('redirect_to'),
            'data' => new AccountAddress()
        ]);
    }

    public function store(Account $account, AccountAddressRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request)) {
        }

        try {
            $this->manageBillingChange($account);
            $account->address()->save(new AccountAddress($this->validated_data));
            $this->responseSuccess("L'adresse est ajoutée.");

            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));

        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {

            return $this->sendResponse();
        }
    }

    public function edit(Account $account, AccountAddress $address): Renderable
    {
        return view('address.account')->with([
            'account' => $account,
            'data' => $address,
            'route' => route('panel.accounts.addresses.update', [$account, $address]),
            'method' => 'put',
            'redirect_to' => request()->input('redirect_to'),
            'title' => 'Éditer une addresse',
            'establishments' => \App\Accessors\Establishments::orderedIdNameArray()
        ]);
    }

    public function update(Account $account, AccountAddress $address, AccountAddressRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request)) {
            return $this->sendResponse();
        }
        try {
            $this->manageBillingChange($account);
            $address->update($this->validated_data);
            $this->responseSuccess("L'adresse est mise à jour.");
        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));
            return $this->sendResponse();
        }
    }

    public function destroy(Account $account, AccountAddress $address): RedirectResponse
    {
        try {
            $address->delete();
            $this->responseSuccess("L'adresse a été supprimée.");
        } catch (Throwable $e)
        {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    private function manageBillingChange(Account $account): void
    {
        if (!empty($this->validated_data['billing'])) {
            AccountAddress::query()->where('user_id', $account->id)->update(['billing' => null]);
        }
    }

}
