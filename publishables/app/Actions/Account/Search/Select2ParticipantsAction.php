<?php

namespace App\Actions\Account\Search;

use App\Accessors\ProgramInterventionOrators;
use App\Accessors\ProgramParticipants;
use MetaFramework\Traits\Responses;

class Select2ParticipantsAction
{

    use Responses;

    public function getParticipantsByEventIdInterventionId(
        ?string $q,
        int     $eventId,
        ?int    $interventionId = null,
        array   $alreadySelectedParticipantIds = [],
        string  $participantType = 'orator',
    ): array
    {

        $r = ProgramParticipants::getParticipantInfo($eventId, $interventionId, $q, $alreadySelectedParticipantIds, $participantType);
        $r = $r->sortBy("name")->values();

        $results = $r->map(function ($participant) use ($interventionId) {
            return [
                "id" => $participant['id'],
                "text" => $participant['name'],
                "info" => [
                    'id' => $participant['id'],
                    'name' => $participant['name'],
                    'desired_transport_management' => $participant['desired_transport_management'],
                    'intervention_status' => $participant['intervention_status'],
                    'allow_pdf_distribution' => (int)$participant['allow_pdf_distribution'],
                    'allow_video_distribution' => (int)$participant['allow_video_distribution'],
                    'moderator_type_id' => $participant['moderator_type_id'],
                    'departure_text' => $participant['departure_text'],
                    'show_departure_text' => $participant['show_departure_text'],
                    'return_text' => $participant['return_text'],
                    'show_return_text' => $participant['show_return_text'],
                ]
            ];
        });

        $this->response['results'] = $results;
        return $this->fetchResponse();
    }
}
