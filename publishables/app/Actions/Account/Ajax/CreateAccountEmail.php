<?php

namespace App\Actions\Account\Ajax;

use App\Http\Controllers\AccountMailController;
use App\Http\Requests\AccountMailRequest;
use App\Models\Account;
use App\Models\AccountMail;
use MetaFramework\Traits\Responses;
use Throwable;

class CreateAccountEmail
{
    use Responses;

    public function __invoke(): array
    {
        $this->enableAjaxMode();

        if (!request()->filled('account_id')) {
            $this->responseWarning("Le compte n'est pas indiqué.");
            return $this->fetchResponse();
        }

        try {
            $account = Account::findOrFail(request('account_id'));
        } catch (Throwable $e) {
            $this->responseException($e, "Le compte n'a pas pu être récupéré");
            return $this->fetchResponse();
        }

        $mailable = (new AccountMailController());
        $mailable->enableAjaxMode();

        $validation = new AccountMailRequest($account);
        $mailable->addValidationRules($validation->rules());
        $mailable->addValidationMessages($validation->messages());
        $mailable->validation();

        try {
            if (request()->has('mails.default')) {
                $mailable->replaceMainEmail($account);
            } else {
                $account->mails()->save(new AccountMail($mailable->validatedData('mails')));
            }
            $mailable->responseSuccess(__('ui.email_added'));
            $mailable->responseElement('email', $mailable->validatedData('mails')['email']);
            $mailable->responseElement('callback', request('callback'));
        } catch (Throwable $e) {
            $mailable->responseException($e);
        } finally {
            return $mailable->fetchResponse();
        }
    }
}
