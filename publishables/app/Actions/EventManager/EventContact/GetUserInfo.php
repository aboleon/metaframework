<?php

namespace App\Actions\EventManager\EventContact;

use App\Accessors\Accounts;
use App\Accessors\Countries;
use App\Accessors\EventContactAccessor;
use App\Actions\Ajax\AjaxAction;

class GetUserInfo extends AjaxAction
{

    public function getUserInfoByEventEmail(): array
    {
        return $this->handle(function (AjaxAction $a) {
            $email = request('email');
            if ( ! $email) {
                $a->responseError("Veuillez renseigner l'email");

                return $a;
            }

            [$event_id] = $this->checkRequestParams(['event_id']);

            $account = Accounts::getAccountByEmail($email);

            if ( ! $account || ! $account->profile) {
                $a->responseElement('user', 'notfound');
                return $a;
            }


            $sLocation             = "";
            $country               = null;
            $participation_type    = null;
            $participation_type_id = 0;


            $ec = EventContactAccessor::getEventContactByEventAndUser($event_id, $account->id);
            if ($ec) {
                $participation_type    = $ec->participationType?->name;
                $participation_type_id = (int)$ec->participationType?->id;
            }

            $address = Accounts::getBillingAddressByAccount($account);
            if ($address) {
                $sLocation .= $address->locality;
                $country   = Countries::getCountryName($address->country_code);
            }

            if ($country) {
                $sLocation .= ', '.$country;
            }

            $account->location           = $sLocation ?: "Localisation non renseignée";
            $account->participation_type = $participation_type;

            $a->responseElement('user', $account);
            $a->responseElement('participation_type_id', $participation_type_id);
        });
    }
}
