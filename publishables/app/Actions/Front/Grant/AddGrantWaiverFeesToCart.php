<?php

namespace App\Actions\Front\Grant;

use App\Accessors\EventContactAccessor;
use App\Actions\Ajax\AjaxAction;
use App\Actions\Front\Cart\FrontCartActions;

class AddGrantWaiverFeesToCart extends AjaxAction
{

    public function addGrantWaiverFeesToCart(): array
    {
        return $this->handle(function () {
            [$event_contact_id] = $this->checkRequestParams(["event_contact_id"]);

            $eventContactAccessor = (new EventContactAccessor())->setEventContact($event_contact_id);


            $deposit = $eventContactAccessor->getPayableGrantDeposit();
            if ($deposit) {
                //(new FrontCartActions())->addGrantWaiverFees($pec->getPreferedGrantFor($ec->id));
                (new FrontCartActions())->addGrantWaiverFees($deposit);
            }

            $this->responseSuccess("ok");

            return $this->fetchResponse();
        });
    }

}
