<?php

namespace App\Actions\EventManager\Program;

use App\Accessors\ProgramInterventions;
use App\Accessors\Programs;
use App\Accessors\ProgramSessions;
use App\Models\Event;
use MetaFramework\Traits\Responses;

class MoveProgramThingAction
{
    use Responses;

    public function moveByArrow(): array
    {
        $this->enableAjaxMode();
        try {
            $result = $this->doMoveByArrow('session' === request('type'), 'up' === request('direction'));
            $this->responseElement('callback', $result['callback']);
            $this->responseElement('tbody', $result['tbody']);

        } catch (\Exception $e) {
            $this->responseException($e);
        }
        return $this->fetchResponse();
    }

    public function moveBySwap(): array
    {
        $this->enableAjaxMode();
        try {

            $type = request('type');
            $id = request('id');
            $newPosition = request('new_position');
            $eventId = request('event_id');


            switch ($type) {
                case "session":
                    ProgramSessions::swapByPosition($id, $newPosition);
                    break;
                case "intervention":
                    ProgramInterventions::swapByPosition($id, $newPosition);
                    break;
                default:
                    throw new \Exception("Invalid type $type");
            }

            ProgramInterventions::refreshStartEndTimes(Event::find($eventId));


            $result = $this->getSyncedProgramByEventId($eventId);
            $this->responseElement('callback', $result['callback']);
            $this->responseElement('tbody', $result['tbody']);


        } catch (\Exception $e) {
            $this->responseException($e);
        }
        return $this->fetchResponse();
    }


    public function doMoveByArrow(bool $isSession, bool $isUp): array
    {
        if ($isSession) {
            $thingId = request('sessionId');
        } else {
            $thingId = request('interventionId');
        }

        $eventId = request('eventId');

        if ($isUp) {
            if ($isSession) {
                ProgramSessions::moveSessionUp($thingId);
            } else {
                ProgramInterventions::moveInterventionUp($thingId);
            }
        } else {
            if ($isSession) {

                ProgramSessions::moveSessionDown($thingId);
            } else {
                ProgramInterventions::moveInterventionDown($thingId);
            }
        }

        ProgramInterventions::refreshStartEndTimes(Event::find($eventId));
        return $this->getSyncedProgramByEventId($eventId);
    }


    private function getSyncedProgramByEventId(int $eventId): array
    {
        $event = Event::findOrFail($eventId);
        $program = Programs::getOrganizerPrintViewCollection($event);
        $tbody = view('events.manager.program.organizer.inc.print_table_body', [
            'program' => $program,
            'format' => 'print',
            'arrows' => true,
            'links' => true,
            'positions' => true,
        ])->render();
        return ['callback' => 'syncProgram', 'tbody' => $tbody];
    }
}
