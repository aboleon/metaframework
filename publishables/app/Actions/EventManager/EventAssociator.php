<?php

namespace App\Actions\EventManager;

use App\Models\EventManager\EventGroup;
use App\Models\EventManager\Accommodation;
use MetaFramework\Traits\Ajax;
use Throwable;

class EventAssociator
{

    use Ajax;

    public function __construct(public string $type, public int $event_id, public array $ids)
    {
        $this->enableAjaxMode();
        $this->fetchInput();
    }

    public function associate(): array
    {
        if (!$this->ids) {
            $this->responseWarning("Aucune information à associer");
            return $this->fetchResponse();
        }


        if (!method_exists($this, $this->type)) {
            $this->responseError("L'association pour le type <b>" . $this->type . "</b> n'est pas définie.");
            return $this->fetchResponse();
        }

        try {
            $this->{$this->type}();
            $this->responseSuccess("L'association a été effectuée.");


        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    private function group(): void
    {
        foreach ($this->ids as $id) {
            EventGroup::firstOrCreate([
                'event_id' => $this->event_id,
                'group_id' => $id
            ]);
        }
    }

    private function hotel(): void
    {
        foreach ($this->ids as $id) {
            Accommodation::firstOrCreate([
                'event_id' => $this->event_id,
                'hotel_id' => $id
            ]);
        }
    }
}
