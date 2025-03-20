<?php

namespace App\Actions\EventManager\Program;

use App\Enum\EventProgramParticipantStatus;
use App\Models\EventContact;
use App\Models\EventManager\Program\EventProgramSession;
use MetaFramework\Traits\Responses;

class AssociateEventContactToSessionAction
{
    use Responses;

    public function __construct(
        private EventContact        $eventContact,
        private EventProgramSession $session
    )
    {
    }

    public function associate(): static
    {
        try {
            $this->session->moderators()->attach($this->eventContact->id, [
                "status" => EventProgramParticipantStatus::PENDING->value,
            ]);
            $this->responseSuccess("Le contact a été associé à la session " . $this->session->name);

        } catch (\Throwable $e) {
            $this->responseException($e);
            $this->responseException($e, "Une erreur est survenue à l'association du contact à la session {$this->session->name}.");
        }
        return $this;

    }

}
