<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountMailRequest;
use App\Models\Account;
use App\Models\AccountMail;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use MetaFramework\Services\Validation\ValidationTrait;
use Throwable;

class AccountMailController extends Controller
{
    use ValidationTrait;

    public function create(Account $account): Renderable
    {
        return view('accounts.mail')->with([
            'route' => route('panel.accounts.mail.store', $account),
            'title' => 'Ajouter une adresse e-mail pour ' . $account->names(),
            'account' => $account,
            'user_route' => 'panel.accounts',
            'redirect_to' => request()->input('redirect_to'),
            'data' => new AccountMail(),
        ]);
    }

    public function store(Account $account, AccountMailRequest $request): RedirectResponse
    {
        if (!$this->ensureDataIsValid($request, 'mails')) {
            return $this->sendResponse();
        }

        try {
            if(request()->has('mails.default')) {
                $this->replaceMainEmail($account);
            } else {
                $account->mails()->save(new AccountMail($this->validated_data['mails']));
            }
            $this->responseSuccess(__('ui.email_added'));
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));

        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            return $this->sendResponse();
        }
    }

    public function edit(Account $account, AccountMail $mail): Renderable
    {
        return view('accounts.mail')->with([
            'account' => $account,
            'data' => $mail,
            'route' => route('panel.accounts.mail.update', [$account, $mail]),
            'method' => 'put',
            'redirect_to' => request()->input('redirect_to'),
            'title' => 'Éditer une adresse e-mail',
        ]);
    }

    public function update(Account $account, AccountMail $mail): RedirectResponse
    {
        $validation = new AccountMailRequest($account, $mail);
        $this->validation_rules = $validation->rules();
        $this->validation_messages = $validation->messages();

        $this->validation();

        try {

            if(request()->has('mails.default')) {
                $this->replaceMainEmail($account);
            } else {
                $mail->update($this->validated_data['mails']);
            }
            $this->responseSuccess("L'adresse e-mail est mise à jour.");
        } catch (Throwable $e) {
            $this->responseException($e);
        } finally {
            $this->redirect_to = request()->input('custom_redirect', route('panel.accounts.edit', $account));
            return $this->sendResponse();
        }
    }


    public function destroy(Account $account, AccountMail $mail): RedirectResponse
    {
        try {
            $mail->delete();
            $this->responseSuccess("L'adresse e-mail a été supprimée");
        } catch (Throwable $e)
        {
            $this->responseException($e);
        }
        return $this->sendResponse();
    }

    public function replaceMainEmail(Account $account): void
    {

        // Copier l'ancienne adresse principale vers account_mails
        if (!AccountMail::where('email', $account->email)->exists()) {
            $account->mails()->save(new AccountMail(['email' => $account->email]));
        }

        // Remplacer l'adresse e-mail principale
        $account->email = $this->validated_data['mails']['email'];
        $account->save();

        $this->redirect_to = route('panel.accounts.edit', $account);

        AccountMail::where('email', $this->validated_data['mails']['email'])->delete();
    }
}
