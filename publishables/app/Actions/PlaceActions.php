<?php

namespace App\Actions;

use App\Http\Controllers\PlaceRoomController;
use App\Http\Requests\PlaceRequest;
use App\Http\Requests\RoomRequest;
use App\Models\Place;
use App\Models\PlaceRoomSetup;
use Illuminate\Support\Facades\DB;
use MetaFramework\Actions\Translator;
use MetaFramework\Services\Validation\ValidationInstance;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\Ajax;
use Throwable;

class PlaceActions
{
    use Ajax;
    use ValidationTrait;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
        $this->fetchCallback();
    }

    public function create(): array
    {
        $request = new PlaceRequest();

        $validation = new ValidationInstance();
        $validation->addValidationRules($request->rules());
        $validation->addValidationMessages($request->messages());
        $validation->validation();


        try {
            $place = Place::create($validation->validatedData('place'));
            $place->address()->create($validation->validatedData('wa_geo'));

            $place->processMedia();

            $this->responseSuccess(__('ui.record_created'));

            $this->responseElement('callback', 'appendDymanicPlace');
            $this->responseElement('selectable', request('selectable') ?: 'event_config__place_id');
            $this->responseElement('place_id', $place->id);
            $this->responseElement('title', $place->name .', '.$validation->validatedData('wa_geo')['locality'].', '.$this->response['input']['wa_geo']['country']);
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function removePlaceRoomSetup(int $id): array
    {
        $this->responseElement('callback', 'ajaxPostDeletePlaceRoomSetup');
        try {
            PlaceRoomSetup::where('id', $id)->delete();
            $this->responseSuccess("La mise en place a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();

    }

    public function find(): array
    {
        try {

            $keywords = explode(" ", (string)request('keyword'));

            $query = DB::table('places as a')
                ->select('a.id', 'a.name', 'b.locality', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(c.name, '$.fr')) as country"))
                ->leftJoin('place_addresses as b', 'a.id', '=', 'b.place_id')
                ->leftJoin('countries as c', 'c.code', '=', 'b.country_code');

            if (!empty($keywords)) {
                $query->where(function ($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $keyword = trim($keyword);
                        if (!empty($keyword)) {
                            $query->orWhere('a.name', 'LIKE', "%{$keyword}%")
                                ->orWhere('b.locality', 'LIKE', "%{$keyword}%")
                                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(c.name, '$.fr')) LIKE ?", ["%{$keyword}%"]);
                        }
                    }
                });
            }

            $results = $query->get()->toArray();

            $this->responseElement('items', $results);
        } catch (Throwable $e) {
            $this->responseException($e);
            $this->responseElement('items', []);
        }

        return $this->fetchResponse();
    }

    public function createRoomFroModal(): array
    {
        $validation = new RoomRequest();
        $this->validation_rules = $validation->rules();
        $this->validation_messages = $validation->messages();

        $this->validation();

        try {

            $place = Place::findOrFail(request('place_id'));
            $room = $place->rooms()->create($this->validatedData());
            (new Translator($room))
                ->update();

            (new PlaceRoomController())->syncRelations($room);

            $this->responseSuccess(__('ui.record_created'));
            $this->responseElement('callback', 'appendDymanicRoom');
            $this->responseElement('room_name', $room->name);
            $this->responseElement('selectable', request('selectable'));
            $this->responseElement('room_id', $room->id);

        }  catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }
}
