<?php

namespace App\Actions\EventManager\Program;

use App\Models\EventManager\Program\EventProgramSession;

class GetSessionInfoAction
{

    public function getSessionInfo(int $sessionId): array
    {
        return EventProgramSession::where('id', $sessionId)
            ->with('room')
            ->first()
            ->toArray();
    }
}