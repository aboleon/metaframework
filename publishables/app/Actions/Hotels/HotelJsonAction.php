<?php

namespace App\Actions\Hotels;

use App\Accessors\HotelServices;
use App\Models\EventManager\Accommodation;
use App\Models\Hotel;

class HotelJsonAction
{
    public function getHotelById(int $hotelId): array
    {
        $hotel = Hotel::with(['address', 'media'])
            ->find($hotelId);

        if ($hotel) {
            $hotelArray = $hotel->toArray();
            $hotelArray['services'] = HotelServices::getServiceSelectableByIds($hotel['services']);
            return $hotelArray;
        }

        return [];
    }

    public function getHotelByEvent(int $eventId, int $hotelId): array
    {
        $accommodation =  Accommodation::with([
                'hotel',
                'hotel.address',
                'hotel.media',
                'service',
                'deposits',
                'roomGroups',
                'contingent',
                'blocked',
                'grant',
                'groups',
            ]
        )
            ->where('event_id', $eventId)
            ->where('hotel_id', $hotelId)
            ->first()
            ->toArray();
        if($accommodation){
            $accommodation['hotel']['services'] = HotelServices::getServiceSelectableByIds($accommodation['hotel']['services']);
            return $accommodation;
        }
        return [];
    }
}