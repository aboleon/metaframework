<?php

namespace App\Actions\SellableDeposit;

use App\Actions\Ajax\AjaxAction;
use App\Enum\EventDepositStatus;
use App\Http\Controllers\MailController;
use App\Models\Invoice;
use App\Models\Order\EventDeposit;

class MakeInvoiceForEventDepositAction extends AjaxAction
{

    public function makeInvoiceForEventDeposit()
    {
        return $this->handle(function () {
            [$eventDepositId] = $this->checkRequestParams(["id"]);

            $deposit = EventDeposit::findOrFail($eventDepositId);

            # Attach invoice
            Invoice::firstOrcreate([
                'order_id' => $deposit->order_id,
            ], [
                'created_by' => $deposit->order->client_id,
            ]);

            $deposit->status = EventDepositStatus::BILLED->value;
            $deposit->save();

            $this->responseSuccess("Facture créée");

            if ($this->hasErrors()) {
                $this->responseError("La création de facture a échoué");
            } else {
                $mc = new MailController();
                $mc->ajaxMode()->distribute('DepositInvoice', $deposit)->fetchResponse();
                if ($mc->hasErrors()) {
                    $this->responseError("Le mail n'a pas pu être envoyé");
                }
            }

            return $this->fetchResponse();
        });
    }

}
