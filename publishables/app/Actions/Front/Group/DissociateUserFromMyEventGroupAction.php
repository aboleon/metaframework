<?php

namespace App\Actions\Front\Group;

use App\Actions\Ajax\AjaxAction;
use App\Models\EventManager\EventGroup\EventGroupContact;
use Auth;

class DissociateUserFromMyEventGroupAction extends AjaxAction
{

    public function dissociate(): array
    {
        return $this->handle(function () {

            $frontUser = Auth::user();
            if (null === $frontUser) {
                throw new \Exception('User not found');
            }

            list($eventGroupContactId) = $this->checkRequestParams(['event_group_contact_id']);
            $eventGroupContact = EventGroupContact::find($eventGroupContactId);
            if (null === $eventGroupContact) {
                throw new \Exception('Event group contact not found');
            }

            if ($eventGroupContact->eventGroup->main_contact_id !== $frontUser->id) {
                throw new \Exception('Vous n\'avez pas les droits pour effectuer cette action');
            }

            if ($eventGroupContact->user_id === $frontUser->id) {
                $this->responseError('Vous ne pouvez pas vous retirer de votre propre groupe.');
                return;
            }

            $eventGroupContact->delete();


            $this->responseSuccess("L'utilisateur a été supprimé de votre groupe.");
        });
    }


}
