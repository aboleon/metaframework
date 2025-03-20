<?php

namespace App\Actions\EventManager\Accommodation;


use App\Models\EventManager\EventGroup;
use App\Models\EventManager\Groups\BlockedGroupRoom;
use Illuminate\Support\Facades\DB;
use MetaFramework\Traits\Ajax;
use MetaFramework\Traits\Responses;
use Throwable;

class BlockGroupRooms
{
    use Ajax;
    use Responses;

    private EventGroup $model;
    private array $data;

    public function __construct(public int $event_group_id)
    {
        $this->setEventGroupId();
    }

    public function process(): array
    {
        if ($this->hasErrors()) {
            return $this->fetchResponse();
        }

        $this->parseData();

        $this->update();

        return $this->fetchResponse();
    }

    private function setEventGroupId(): void
    {
        try {
            $this->model = EventGroup::findOrFail($this->event_group_id);
        } catch (Throwable) {
            $this->responseError("Aucun évènement ne peut pas être retrouvé avec l'identifiant #" . $this->event_group_id);
        }
    }

    private function parseData()
    {
        if (!request()->has('group')) {
            $this->responseError("Aucun rattachement à traiter");
        }

        $groups = array_unique(request('group'));
        foreach ($groups as $group) {
            foreach (request($group) as $values) {
                $subdata = [];
                $subdata['event_group_id'] = request('event_group_id');
                $subdata['event_accommodation_id'] = $values['hotel_id'];
                $subdata['room_group_id'] = $values['room_group_id'];
                $subdata['group_key'] = $group;
                $subdata['date'] = $values['date'];
                $subdata['total'] = $values['total'];
                $this->data[] = $subdata;
            }
        }
    }

    private function update(): void
    {
        DB::beginTransaction();

        try {
            BlockedGroupRoom::where('event_group_id', $this->model->id)->delete();

            if ($this->data) {
                BlockedGroupRoom::insert($this->data);
            }

            DB::commit();
            $this->responseSuccess("Les modificatios ont été prises en compte");

        } catch (Throwable $e) {
            $this->responseException($e);
            DB::rollBack();
        }


    }
}
