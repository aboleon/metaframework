<?php

namespace App\Actions\EventManager\Invitation;

use App\Enum\ApprovalResponseStatus;
use App\Models\EventManager\Sellable;
use App\Models\EventManager\Sellable\EventContactSellableServiceChoosable;
use MetaFramework\Traits\Responses;
use Throwable;

class CreateInvitationAction
{
    use Responses;

    public function __construct()
    {
        $this->enableAjaxMode();
    }

    public function createInvitation(): array
    {
        $invitationId = request('invitation_id');
        $participantId = request('participant_id');
        $response = request('response');
        $quantity = request('quantity');

        if (null === $participantId) {
            $this->responseError("Veuillez renseigner un participant.");
            goto end;
        }


        if (null === $invitationId) {
            $this->responseError("Invitation non renseignée.");
            goto end;
        }

        $sellable = Sellable::find($invitationId);
        if (!$sellable) {
            $this->responseError("Invitation #$invitationId non trouvée.");
            goto end;
        }

        if (!$sellable->is_invitation) {
            $this->responseError("Les inscriptions de ce type ne sont disponibles que pour les prestations choix.");
            goto end;
        }

        if (!$sellable->stock_unlimited) {
            if (!$sellable->stock) {
                $this->responseError("Il faut configurer un stock initial ou indiquer si le stock est illimité.");
                goto end;
            }
        }


        $invitationQuantityEnabled = $sellable->invitation_quantity_enabled;


        if (null === $response) {
            $this->responseError("Veuillez renseigner une réponse.");
            goto end;
        }

        if ("1" === $response && $invitationQuantityEnabled && null === $quantity) {
            $this->responseError("Veuillez renseigner une quantité.");
            goto end;
        }

        $ecc = EventContactSellableServiceChoosable::where('event_contact_id', $participantId)
            ->where('choosable_id', $invitationId)
            ->first();
        if ($ecc) {
            $this->responseError("L'invitation pour ce participant existe déjà.");
            goto end;
        }


        try {

            $status = $response === "1" ? ApprovalResponseStatus::VALIDATED->value : ApprovalResponseStatus::DENIED->value;
            $ecc = new EventContactSellableServiceChoosable();
            $ecc->event_contact_id = $participantId;
            $ecc->choosable_id = $invitationId;
            $ecc->status = $status;
            $ecc->invitation_quantity_accepted = ("2" === $quantity) ? 1 : null;
            $ecc->save();


            // TODO : il y avait ici une dérémentation du stock


            $this->responseSuccess("L'invitation a été créée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }


        end:
        return $this->fetchResponse();
    }
}
