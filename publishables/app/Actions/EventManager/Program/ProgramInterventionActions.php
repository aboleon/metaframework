<?php

namespace App\Actions\EventManager\Program;

use App\Models\EventManager\Program\EventProgramIntervention;

class ProgramInterventionActions
{

    public function getInterventionsBySession(int $sessionId): array
    {
        return EventProgramIntervention::with("room.place")
            ->where('event_program_session_id', $sessionId)
            ->orderBy("start")
            ->get()
            ->toArray();
    }
    public function getInterventionsByContainer(int $containerId): array
    {
        return EventProgramIntervention::with(["room.place", "session"])
            ->whereHas("session", function ($query) use ($containerId) {
                $query->where("event_program_day_room_id", $containerId);
            })
            ->orderBy("start")
            ->get()
            ->toArray();
    }

    public function getInterventionsByEvent(int $eventId): array
    {
        return EventProgramIntervention::with(["room.place", "session.programDay"])
            ->whereHas("session.programDay", function ($query) use ($eventId) {
                $query->where("event_id", $eventId);
            })
            ->orderBy("start")
            ->get()
            ->toArray();
    }
}