<?php

namespace App\Actions\SellableDeposit;

use App\Actions\Ajax\AjaxAction;
use App\Actions\Front\Transaction;
use App\Http\Controllers\MailController;
use App\Models\Order\EventDeposit;
use App\Services\PaymentProvider\PayBox\Paybox;
use Throwable;

class ReimburseEventDepositAction extends AjaxAction
{

    public function reimburseEventDeposit()
    {
        return $this->handle(function () {
            try {
                $eventDeposit = EventDeposit::findOrFail((int)request('id'));
            } catch (Throwable $e) {
                $this->responseException($e, "La caution à rembourser n'a pas pu être identifée.");

                return $this->fetchResponse();
            }

            $reimbursed = (new Paybox())->sendReimbursementRequest($eventDeposit);

            if ($reimbursed->isSuccessful()) {
                $eventDeposit->reimbursed_at = now();
                $eventDeposit->save();
            } else {
                switch ($reimbursed->responseCode()) {
                    case "00001":
                        /**
                         * from paybox docs:
                         * La connexion au centre d’autorisation a échoué ou une erreur interne est survenue. Dans ce cas, il est souhaitable de faire une tentative sur le site secondaire : ppps1.paybox.com.
                         */
                        $this->responseError("Le remboursement a échoué pour des raisons techniques du côté du serveur Paybox");
                        break;
                    default:
                        $this->responseError("Le remboursement a échoué avec un code ".$reimbursed->responseCode()." - ".$reimbursed->responseComment());
                }
            }
            if ( ! $this->hasErrors()) {
                $this->responseSuccess("Remboursement effectué");
                $mc = new MailController();
                $mc->ajaxMode()->distribute('ReimbursementNotice', $eventDeposit)->fetchResponse();

                if ($mc->hasErrors()) {
                    $this->responseError("Le mail n'a pas pu être envoyé");
                }
            }

            return $this->fetchResponse();
        });
    }

}
