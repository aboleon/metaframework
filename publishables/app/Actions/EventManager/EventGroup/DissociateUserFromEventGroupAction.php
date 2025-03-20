<?php

namespace App\Actions\EventManager\EventGroup;

use App\Models\EventManager\EventGroup;
use App\Models\EventManager\EventGroup\EventGroupContact;
use App\Models\User;
use MetaFramework\Traits\Ajax;

class DissociateUserFromEventGroupAction
{
    use Ajax;

    public function dissociate(): array
    {
        $this->enableAjaxMode();

        $eventGroupId = (int)request()->input('event_group_id');
        $userId = (int)request()->input('user_id');


        $eg = EventGroup::find($eventGroupId);
        $user = User::find($userId);

        if (!$eg or !$user) {
            $this->responseError("Le group ou le compte utilisateur n'ont pas été trouvés !");
            return $this->fetchResponse();
        }

        $eventGroupContact = EventGroupContact::where('event_group_id', $eventGroupId)
            ->where('user_id', $userId)
            ->first();
        $eventGroupContact->delete();


        $groupName = $eg->group->name;
        $this->responseSuccess("L'utilisateur a été supprimé du groupe $groupName.");

        return $this->fetchResponse();

    }


}
