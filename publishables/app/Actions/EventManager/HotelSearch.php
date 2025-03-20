<?php

namespace App\Actions\EventManager;

use App\Models\EventManager\Accommodation;
use App\Models\Hotel;
use MetaFramework\Traits\Ajax;
use Throwable;

class HotelSearch
{

    use Ajax;

    public function __construct(public string $keyword, public int $event_id)
    {
        $this->enableAjaxMode();
        $this->fetchInput();
    }

    public function find(array $options = []): array
    {
        $select = $options['select'] ?? ['hotels.id', 'hotels.name', 'b.locality'];
        $useCallback = $options['useCallback'] ?? true;
        $itemsKey = $options['itemsKey'] ?? "items";

        $this->responseElement('event_id', $this->event_id);
        if ($useCallback) {
            $this->responseElement('callback', $this->response['input']['callback']);
        }

        try {
            $hotels_id = Accommodation::where('event_id', $this->event_id)->pluck('hotel_id')->toArray();

            $query = Hotel::query()
                ->select(...$select);

            if ($hotels_id) {
                $query = $query->whereNotIn('hotels.id', $hotels_id);
            }

            $items = $query->leftJoin('hotel_address as b', 'hotels.id', '=', 'b.hotel_id')
                ->where(function ($where) {
                    $where->where('hotels.name', 'like', '%' . $this->keyword . '%')
                        ->orWhere('b.locality', 'like', '%' . $this->keyword . '%');
                })
                ->get()
                ->toArray();


            $this->responseElement($itemsKey, $items);


        } catch (Throwable $e) {
            $this->responseException($e);
            $this->responseElement('items', []);
        }

        return $this->fetchResponse();
    }
}
