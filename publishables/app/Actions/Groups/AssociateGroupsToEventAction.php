<?php

namespace App\Actions\Groups;

use App\Accessors\GroupAccessor;
use App\Actions\EventManager\EventAssociator;

class AssociateGroupsToEventAction
{
    public function associateGroupsToEvent(): array
    {

        $eventId = request('associateGroupsToEvent.event_id');
        if(null === $eventId){
            $eventId = request('event_id');
        }


        $mode = request('mode');
        if ('all' === $mode) {
            $ids = GroupAccessor::allIds();
        } else {
            $ids = explode(',', request('ids'));
            $ids = array_unique($ids);
        }


        return (new EventAssociator(
            type: "group",
            event_id: $eventId,
            ids: $ids
        ))->associate();
    }
}
