<?php

namespace App\Actions\EventManager;

use App\Accessors\ParticipationTypes;
use App\Models\Event;
use App\Models\EventManager\Accommodation;
use MetaFramework\Traits\Ajax;
use Throwable;

class HotelAssociate
{

    use Ajax;

    public function __construct(public int $hotel_id, public int $event_id)
    {
        $this->enableAjaxMode();
        $this->fetchInput();
    }

    public function associate(): array
    {
        try {
            $event = Event::findOrFail($this->event_id);
            $id = Accommodation::create([
                'hotel_id' => $this->hotel_id,
                'event_id' => $this->event_id,
                'participation_types' => implode(',', $event->participations->pluck('id')->toArray()),
            ]);
            $this->responseElement('id', $id);
            $this->responseSuccess("L'hôtel a été associé à l'évènement.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }
}
