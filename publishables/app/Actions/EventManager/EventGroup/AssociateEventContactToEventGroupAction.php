<?php

namespace App\Actions\EventManager\EventGroup;

use App\Actions\Ajax\AjaxAction;
use App\Models\EventContact;
use App\Models\EventManager\EventGroup;
use Throwable;

class AssociateEventContactToEventGroupAction extends AjaxAction
{

    public function associateEventContactsToEventGroup(array $eventContactIds, ?int $eventGroupId = null): array
    {
        return $this->handle(function () use ($eventContactIds, $eventGroupId) {

            $isValid = true;
            if (null === $eventGroupId) {
                list($eventGroupId) = $this->checkRequestParams(['event_group_id']);
            }
            $userIdsToAssociate = [];
            foreach ($eventContactIds as $eventContactId) {
                $eventContact = EventContact::find($eventContactId);
                if (null === $eventContact) {
                    throw new \Exception("Contact not found with id $eventContactId");
                }

                $eventId = $eventContact->event_id;
                $userId = $eventContact->user_id;
                $userIdsToAssociate[] = $userId;


                $userEventGroups = EventGroup::where('event_id', $eventId)
                    ->whereHas('eventGroupContacts', function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->get();
                if ($userEventGroups->count() > 0) {
                    $groupName = $userEventGroups->first()->group->name;
                    $this->responseError("Au moins un des contacts ({$eventContact->user->fullName()}) que vous avez sélectionné est déjà affecté au groupe {$groupName}.");
                    $isValid = false;
                    break;
                }
            }

            if ($isValid) {
                // at this point, we are sure that none of the contacts are associated to the group
                foreach ($userIdsToAssociate as $userId) {
                    $egc = new EventGroup\EventGroupContact();
                    $egc->event_group_id = $eventGroupId;
                    $egc->user_id = $userId;
                    $egc->save();
                }
                $this->responseSuccess('Les contacts sont bien affectés au groupe pour cet événement.');
            }
        });
    }

    public function associateEventContactToEventGroup(?int $eventContactId = null, ?int $eventGroupId = null): array
    {

        $this->enableAjaxMode();

        $eventContactId = $eventContactId ?? request('event_contact_id');
        $eventGroupId = $eventGroupId ?? request('event_group_id');

        $eventGroup = EventGroup::find($eventGroupId);
        if (!$eventGroup) {
            $this->responseError('Group not found');
            return $this->fetchResponse();
        }

        $ec = EventContact::find($eventContactId);
        if (!$ec) {
            $this->responseError('Event contact not found');
            return $this->fetchResponse();
        }

        $userId = $ec->user_id;
        $eventId = $ec->event_id;


        $userEventGroups = EventGroup::where('event_id', $eventId)
            ->whereHas('eventGroupContacts', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();


        if ($userEventGroups->count() > 0) {
            $groupName = $userEventGroups->first()->group->name;
            $this->responseError("L'utilisateur est déjà affecté au groupe \"$groupName\" pour cet événement.");
            return $this->fetchResponse();
        }


        try {

            $model = new EventGroup\EventGroupContact();
            $model->event_group_id = $eventGroupId;
            $model->user_id = $userId;
            $model->save();
            $this->responseSuccess("Le contact est bien affecté au groupe \"{$eventGroup->group->name}\"");

            $this->responseElement('user_id', $model->user_id);
            $this->responseElement('user_name', $model->user->names());

            return $this->fetchResponse();

        } catch (Throwable $e) {
            $this->responseException($e);
        }
        return $this->fetchResponse();
    }


}
