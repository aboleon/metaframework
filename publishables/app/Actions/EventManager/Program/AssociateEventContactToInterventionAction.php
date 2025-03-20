<?php

namespace App\Actions\EventManager\Program;

use App\Enum\EventProgramParticipantStatus;
use App\Models\EventContact;
use App\Models\EventManager\Program\EventProgramIntervention;
use MetaFramework\Traits\Responses;

class AssociateEventContactToInterventionAction
{
    use Responses;

    public function __construct(
        private EventContact     $eventContact,
        private EventProgramIntervention $intervention
    )
    {
    }

    public function associate(): static
    {
        try {
            $this->intervention->orators()->attach($this->eventContact->id, [
                "status" => EventProgramParticipantStatus::PENDING->value,
            ]);
            $this->responseSuccess("Le contact a été associé à l'intervention " . $this->intervention->name);

        } catch (\Throwable $e) {
            $this->responseException($e);
            $this->responseException($e, "Une erreur est survenue à l'association du contact à l'intervention {$this->intervention->name}.");
        }
        return $this;

    }

}
