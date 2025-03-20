<?php

namespace App\Actions\Groups;

use App\Actions\Ajax\AjaxAction;
use App\Models\EventContact;
use App\Models\EventManager\EventGroup;

class MakeMainContactAction extends AjaxAction
{

    public function makeMainContactOfEventGroup(): array
    {
        return $this->handle(function () {
            [$eventGroupId] = $this->checkRequestParams(['event_group_id']);
            $userId = request('user_id');

            $eventGroup = EventGroup::find($eventGroupId);

            if ( ! $eventGroup) {
                throw new \Exception("Le groupe event $eventGroupId n'existe pas.");
            }

            $eventGroup->main_contact_id = $userId;
            $eventGroup->save();
            $groupName = $eventGroup->group->name;
            if ( ! $userId) {
                $msg = "Le contact principal du groupe $groupName a été dissocié.";
            } else {
                $userName = $eventGroup->mainContact->fullName();
                $msg      = "Le contact $userName a été défini comme contact principal du groupe $groupName.";
                // Nuller les demandes d'être group manager des autres sinon ils sont bloqués en front
                EventContact::where(fn($where) => $where->where('user_id', '!=', $userId)->where('event_id', $eventGroup->event_id))->update(['fo_group_manager_request_sent' => null]);
            }

            $this->responseSuccess($msg);
        });
    }

}
