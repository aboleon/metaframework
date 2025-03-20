<?php

namespace App\Actions\EventManager;

use App\Accessors\Accounts;
use App\Accessors\EventContactAccessor;
use App\Enum\EventDepositStatus;
use App\Enum\OrderClientType;
use App\Enum\OrderMarker;
use App\Enum\OrderOrigin;
use App\Enum\OrderType;
use App\Models\CustomPaymentCall;
use App\Models\Event;
use App\Models\EventContact;
use App\Models\Order;
use App\Models\Order\Cart\GrantDepositCart;
use App\Models\Order\EventDeposit;
use App\Services\PaymentProvider\PayBox\Paybox;
use App\Services\Pec\PecParser;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use MetaFramework\Accessors\VatAccessor;
use MetaFramework\Traits\Ajax;
use MetaFramework\Traits\Responses;
use Str;
use Throwable;

class GrantActions
{
    use Ajax;
    use Responses;

    protected EventDeposit $deposit;
    protected CustomPaymentCall $paymentCall;


    public function updateEligibleStatusForContacts(Event $event): self
    {
        try {
            $event->load('contacts.profile', 'contacts.address');

            $pec = new PecParser($event, $event->contacts);
            $pec->calculate();

            EventContact::whereIn('id', $pec->getEligibileContactIds())->update(['is_pec_eligible' => 1]);
            EventContact::whereIn('id', $pec->getNonEligibleContactIds())->update(['is_pec_eligible' => null]);

            $this->responseSuccess("La mise à jour de l'égilibité Grant des contacts est effectuée.");
        } catch (Throwable) {
            $this->responseError("La mise à jour de l'égilibité Grant des contacts à échoué.");
        }

        return $this;
    }

    public function updateEligibleStatusForSingleContact(Event $event, EventContact $contact): self
    {
        try {
            $pecParser = new PecParser($event, collect()->push($contact));
            $pecParser->calculate();

            $contact->is_pec_eligible = $pecParser->hasGrants($contact->id) ? 1 : null;
            $contact->save();

            $this->responseSuccess("La mise à jour de l'égilibité Grant est effectuée.");
        } catch (Throwable) {
            $this->responseError("La mise à jour de l'égilibité Grant à échoué.");
        }

        return $this;
    }

    public function setEligiblesToNull(Event $event): self
    {
        EventContact::where('event_id', $event->id)->update(['is_pec_eligible' => null]);

        $this->responseSuccess("Le statut éligibilité de tous les contacts a été remis à zéro.");

        return $this;
    }

    /**
     * Création d'une caution depuis le BO
     *
     * @param  array         $grant  [id,title,deposit,vat_id]
     * @param  EventContact  $eventContact
     *
     * @return self
     */
    public function attachDepositToEventContact(array $grant, EventContact $eventContact): self
    {
        // Vérifier s'il n'y a pas déjà une caution
        if ($eventContact->grantDeposit) {
            $this->deposit = $eventContact->grantDeposit;

            // Si c'est une TEMP (front)
            if ($this->deposit->status == EventDepositStatus::TEMP->value) {
                $this->deposit->status = EventDepositStatus::default();
                $this->deposit->save();

                $this->makePaymentCall();

                $this->responseNotice(
                    "Une caution de ".$this->deposit->total_net + $this->deposit->total_vat." € du grant ".$this->deposit->shoppable_label." a été rattachée pour paiment. Vous pour vous <a href='".route('custompayment.form', ['uuid' => Crypt::encryptString($this->paymentCall->id)])."' class='btn btn-sm btn-secondary'>voir la page de paiement</a> ou envoyer le lien de paiement depuis la page des <a target='_blank' class='btn btn-sm btn-secondary' href='".route(
                        'panel.manager.event.event_deposit.index',
                        $eventContact->event_id,
                    )."'>cautions</a>",
                );

                return $this;
            }

            $eventContactAccessor = (new EventContactAccessor())->setEventContact($eventContact);
            $message              = "Ce participant a déjà une caution Grant ";

            if ($eventContactAccessor->hasPaidGrantDeposit()) {
                $message .= " et elle a été payée.";
                if ( ! $eventContact->pec_enabled) {
                    $eventContact->pec_enabled = 1;
                    $eventContact->save();
                    $this->responseSuccess($message);

                    return $this;
                }
            } elseif ($this->deposit->status == EventDepositStatus::REFUNDED->value) {
                $message .= " et elle a remboursée.";
            } else {
                $message .= "qui est en attente de paiement.";
            }

            $this->responseError($message);

            return $this;
        }

        try {
            $this->makeGrantDeposit($eventContact, $grant);

            if ($this->hasErrors()) {
                return $this;
            }

            // Create Deposit Payment Call
            $this->makePaymentCall();

            $this->responseNotice(
                "Une caution de ".$grant['deposit']." € du grant ".$grant['title']." a été rattachée pour paiment. Vous pour vous <a href='".route('custompayment.form', ['uuid' => Crypt::encryptString($this->paymentCall->id)])."' class='btn btn-sm btn-secondary'>voir la page de paiement</a> ou envoyer le lien de paiement depuis la page des <a target='_blank' class='btn btn-sm btn-secondary' href='".route('panel.manager.event.event_deposit.index', $eventContact->event_id)."'>cautions</a>",
            );
        } catch (Throwable $e) {
            $this->responseException($e, "La caution grant n'a pas pu être rattachée. Activation PEC annulée.");
        }

        return $this;
    }

    private function makePaymentCall(): self
    {
        $this->paymentCall = new CustomPaymentCall([
            'provider' => (new Paybox())->signature()['id'],
            'total'    => $this->deposit->total_net + $this->deposit->total_vat,
        ]);

        $this->deposit->paymentCall()->save($this->paymentCall);
        Log::info('CustomPaymentCall generated.');

        return $this;
    }

    public function makeGrantDeposit(EventContact $eventContact, array $grant, string $state = EventDepositStatus::UNPAID->value): self
    {
        // Invoiceable
        $address = (new Accounts($eventContact->account))->billingAddress();

        if ( ! $address) {
            $this->responseError("La caution grant ne peut pas être rattachée car ce participant n'a pas d'adresse de facturation. Activation PEC annulée.");

            return $this;
        }

        // Create Order
        $order              = new Order();
        $order->uuid        = Str::uuid();
        $order->marker      = OrderMarker::GHOST->value;
        $order->origin      = OrderOrigin::BACK->value;
        $order->type        = OrderType::GRANTDEPOSIT->value;
        $order->event_id    = $eventContact->event_id;
        $order->client_id   = $eventContact->user_id;
        $order->client_type = OrderClientType::CONTACT->value;
        $order->total_net   = VatAccessor::netPriceFromVatPrice($grant['deposit'], $grant['vat_id']);
        $order->total_vat   = VatAccessor::vatForPrice($grant['deposit'], $grant['vat_id']);
        $order->created_by  = auth()->id();
        $order->save();

        $order->invoiceable()->save(new Order\Invoiceable([
            'account_id'    => $eventContact->user_id,
            'account_type'  => OrderClientType::CONTACT->value,
            'address_id'    => $address->id,
            'company'       => $address->company,
            'first_name'    => $eventContact->account->first_name,
            'last_name'     => $eventContact->account->last_name,
            'postal_code'   => $address->postal_code,
            'country_code'  => $address->country_code,
            'street_number' => $address->street_number,
            'locality'      => $address->locality,
            'cedex'         => $address->cedex,
            'route'         => $address->route,
            'text_address'  => $address->text_address,
        ]));


        // Create deposit
        $this->deposit = $order->deposits()->save(
            new EventDeposit([
                'event_id'                     => $eventContact->event_id,
                'shoppable_id'                 => $grant['id'],
                'shoppable_type'               => OrderType::GRANTDEPOSIT->value,
                'vat_id'                       => $grant['vat_id'],
                'total_net'                    => VatAccessor::netPriceFromVatPrice($grant['deposit'], $grant['vat_id']),
                'total_vat'                    => VatAccessor::vatForPrice($grant['deposit'], $grant['vat_id']),
                'event_contact_id' => $eventContact->id,
                'status'                       => $state,
                'shoppable_label'              => $grant['title'],
            ]),
        );


        // Create deposit shopping cart
        $order->grantDeposit()->save(
            new GrantDepositCart([
                'event_deposit_id'             => $this->deposit->id,
                'event_grant_id'               => $grant['id'],
                'vat_id'                       => $grant['vat_id'],
                'unit_price'                   => $grant['deposit'],
                'total_net'                    => VatAccessor::netPriceFromVatPrice($grant['deposit'], $grant['vat_id']),
                'total_vat'                    => VatAccessor::vatForPrice($grant['deposit'], $grant['vat_id']),
                'event_contact_id' => $eventContact->id,
                'quantity'                     => 1,
            ]),
        );

        return $this;
    }

    public function getDeposit(): EventDeposit
    {
        return $this->deposit;
    }
}
