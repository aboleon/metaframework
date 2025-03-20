<?php

namespace App\Actions\Order;

use App\Accessors\Accounts;
use App\Accessors\EventContactAccessor;
use App\Accessors\GroupAccessor;
use App\Enum\OrderClientType;
use App\Http\Requests\OrderRequest;
use App\Models\Account;
use App\Models\EventContact;
use App\Models\EventManager\EventGroup;
use App\Models\Group;
use App\Models\Order;
use MetaFramework\Accessors\Countries;
use MetaFramework\Traits\Ajax;

class OrderInvoiceableActions
{

    use Ajax;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
        $this->fetchCallback();

    }

    public function fetchAccountFromDatabase(): self
    {
        $account_id = (int)request('order_payer_id');
        $account = Account::firstWhere('id', $account_id);

        if (!$account) {
            $this->responseWarning("Aucun compte client n'a été trouvé pour #" . $account_id);
            return $this;
        }

        $account->load(['profile', 'address', 'phones']);
        $accountAccessor = (new Accounts($account));

        $address = $accountAccessor->billingAddress();
        $this->responseElement('account', $account);
        $this->responseElement('accountName', $account->last_name);
        $this->responseElement('accountAddress', $address);
        $this->responseElement('accountAddressCountry', Countries::getCountryNameByCode($address?->country_code));
        $this->responseElement('accountCompany', $account->company);
        $this->responseElement('accountPhone', Accounts::getDefaultPhoneNumberByAccount($account)?->formatNational());
        $this->responseElement('service', $account->profile->service);
        $this->responseElement('participationType', EventContactAccessor::getData((int)request('event_id'), $account_id));


        $eventContact = EventContact::where(['event_id' => request('event_id'), 'user_id' => request('allocation_account_id')])->first();

        if ($eventContact) {
            $eventContactAccessor = (new EventContactAccessor())->setEventContact($eventContact);
            $this->responseElement('booked_pec_services', $eventContactAccessor->getBookedPecServices());
        }

        return $this;
    }

    public static function attachAccountToOrder(Order $order, OrderRequest $request)
    {

    }

    public function fetchGroupFromDatabase(): self
    {
        $request_id = (int)request('order_payer_id');
        $event_id = (int)request('event_id');

        if (empty($event_id)) {
            $this->responseWarning("L'identifiant évènement n'a pas été correctement transmis.");
            return $this;
        }

        $account = Group::firstWhere('id', $request_id);

        if (!$account) {
            $this->responseWarning("Aucun compte groupe n'a été trouvé pour #" . $request_id);
            return $this;
        }

        $account->load(['address']);
        $accountAccessor = (new GroupAccessor($account));

        $address = $accountAccessor->billingAddress();
        $this->responseElement('account', $account);
        $this->responseElement('accountName', $account->name);
        $this->responseElement('accountAddress', $address);
        $this->responseElement('accountAddressCountry', Countries::getCountryNameByCode($address?->country_code));
        $this->responseElement('accountCompany', $account->company);
        $this->responseElement('service', '');
        $this->responseElement('groupParticipants', $accountAccessor->getParticipantsForEvent($event_id));
        $this->responseElement('event_group_id', EventGroup::query()->where(['group_id' => $request_id, 'event_id' => (int)request('event_id')])->value('id'));

        return $this;
    }



    public function fetchPayerFromDatabase(): self
    {
        $type = (string)request('order_client_type');
        if (!in_array($type, OrderClientType::baseGroups())) {
            $this->responseError("Le type de client ne peut être qu'un Contact ou un Groupe.");
        }

        $this->responseElement('callback', 'setAccountAsClient');

        return match ($type) {
            OrderClientType::CONTACT->value => $this->fetchAccountFromDatabase(),
            OrderClientType::GROUP->value => $this->fetchGroupFromDatabase(),
            default => $this
        };
    }
}
