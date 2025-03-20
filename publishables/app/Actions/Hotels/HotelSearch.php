<?php

namespace App\Actions\Hotels;

use App\Models\EventManager\Accommodation;
use App\Models\Hotel;
use Illuminate\Support\Facades\DB;
use MetaFramework\Traits\Ajax;
use Throwable;

class HotelSearch
{

    use Ajax;

    public function __construct(public string $keyword)
    {
        $this->enableAjaxMode();
        $this->fetchInput();
    }

    public function find(): array
    {
        $this->responseElement('callback', $this->response['input']['callback']);

        try {


            $keywords = explode(" ", $this->keyword);

            $query = DB::table('hotels as a')
                ->select('a.id', 'a.name', 'b.locality', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(c.name, '$.fr')) as country"))
                ->leftJoin('hotel_address as b', 'a.id', '=', 'b.hotel_id')
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
}
