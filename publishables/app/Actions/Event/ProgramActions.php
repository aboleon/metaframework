<?php

namespace App\Actions\Event;

use App\Helpers\DateHelper;
use App\Models\EventManager\Program\EventProgramDayRoom;
use MetaFramework\Traits\Responses;

class ProgramActions
{
    use Responses;

    public function getProgramDaysByEventStartEndDate(string $startDate, string $endDate, int $eventId): array
    {

        try {

            $daysAndHours = [];
            $oldDaysAndHours = [];

            if ($eventId) {
                $events = EventProgramDayRoom::where('event_id', $eventId)->get();

                if ($events->isNotEmpty()) {
                    foreach($events as $eventProgramDay) {
                        $day = $eventProgramDay->datetime_start?->format('d/m/Y');
                        if (!$day) {
                            continue;
                        }
                        $oldDaysAndHours[$day] = [
                            "day" => $day,
                            "hour" => $eventProgramDay->datetime_start->format('H:i'),
                        ];
                    };
                }
            }

            $days = (new DateHelper)->listDaysBetweenDates($startDate, $endDate, 'd/m/Y');
            $this->pushMessages($days);

            foreach ($days->fetchResponseElement('days') as $day) {
                $daysAndHours[] = $oldDaysAndHours[$day] ?? [
                    "day" => $day,
                    "hour" => "09:00",
                ];
            }

            $this->responseElement('callback', 'refreshProgramListDays');
            $this->responseElement("days", $daysAndHours);
        } catch (\Exception $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }
}
