<?php

namespace App\Actions\EventManager\Program;

use App\Accessors\PlaceRooms;
use App\Models\EventManager\Program\EventProgramDayRoom;
use App\Models\PlaceRoom;
use MetaFramework\Traits\Responses;

class ProgramDayRoomsAction
{
    use Responses;


    public function getProgramDayRooms(): array
    {
        $this->enableAjaxMode();
        $eventId = request('event_id');


        try {
            $dayRooms = EventProgramDayRoom::with('room.place')
                ->where('event_id', $eventId)
                ->get();

            $days = $dayRooms->map(function ($dayRoom) {
                $datetime = $dayRoom->datetime_start;
                $day = $datetime->format(config('app.date_display_format'));
                $hour = $datetime->format('H:i');

                $place_id = $dayRoom->room->place->id;
                $rooms = PlaceRooms::selectableArray($place_id);

                return [
                    'day' => $day,
                    'hour' => $hour,
                    'room_id' => $dayRoom->room_id,
                    'place_id' => $place_id,
                    'rooms' => $rooms,
                ];
            })->sortBy(function ($dayRoom) {
                return $dayRoom['day'] . ' ' . $dayRoom['hour'];
            })->values()->all();

            $this->responseElement("days", $days);
        } catch (\Exception $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function getPlaceIdRoomIdPlaceRoomsSelectableByEventProgramDayRoomId(): array
    {

        $this->enableAjaxMode();
        $eventProgramDayRoomId = request('event_program_day_room_id');
        $roomId = EventProgramDayRoom::find($eventProgramDayRoomId)->room_id;
        $placeId = PlaceRoom::find($roomId)->place_id;
        $rooms = PlaceRooms::selectableArray($placeId);
        $this->responseElement("place_id", $placeId);
        $this->responseElement("room_id", $roomId);
        $this->responseElement("rooms", $rooms);
        return $this->fetchResponse();
    }
}
