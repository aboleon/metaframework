<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountCardRequest;
use App\Models\Account;
use App\Models\AccountCard;
use MetaFramework\Traits\DateManipulator;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class AccountCardController extends Controller
{
    use DateManipulator;
    use ValidationTrait;

    /**
     * Show the form for creating a new resource.
     */
    public function create(Account $account): Renderable
    {
        return view('accounts.card')->with([
            'route' => route('panel.accounts.cards.store', $account),
            'title' => 'Ajouter une carte de fidélité pour ' . $account->names(),
            'account' => $account,
            'user_route' => 'panel.accounts',
            'redirect_to' => request()->input('redirect_to'),
            'data' => new AccountCard()
        ]);
    }


    public function store(Account $account, AccountCardRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request, 'cards')) {
            return $this->sendResponse();
        }

        try {

            if (null !== $this->validated_data['cards']['expires_at']) {
                $this->validated_data['cards']['expires_at'] = $this->parseDate($this->validated_data['cards']['expires_at']);
            }

            $account->cards()->save(new AccountCard($this->validated_data['cards']));
            $this->responseSuccess("Le document est ajouté.");


        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));
            return $this->sendResponse();
        }
    }

    public function edit(Account $account, AccountCard $card): Renderable
    {
        return view('accounts.card')->with([
            'account' => $account,
            'data' => $card,
            'route' => route('panel.accounts.cards.update', [$account, $card]),
            'method' => 'put',
            'redirect_to' => request()->input('redirect_to'),
            'title' => 'Éditer une carte de fidélité',
        ]);
    }

    public function update(Account $account, AccountCard $card, AccountCardRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request, 'cards')) {
            return $this->sendResponse();
        }

        try {
            $this->validated_data['cards']['expires_at'] = $this->parseDate($this->validated_data['cards']['expires_at']);
            $card->update($this->validated_data['cards']);
            $this->responseSuccess("La carte de fidélité est mise à jour.");
        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));
            return $this->sendResponse();
        }
    }


    public function destroy(Account $account, AccountCard $card): RedirectResponse
    {
        try {
            $card->delete();
            $this->responseSuccess("La carte de fidélité a été supprimé");
        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }
}
