<?php

namespace App\Actions\EventManager;

use App\Models\EventManager\Accommodation\BlockedRoom;
use App\Models\EventManager\Accommodation\Contingent;
use App\Models\EventManager\Accommodation\Grant;
use App\Models\EventManager\Accommodation\Room;
use App\Models\EventManager\Accommodation\RoomGroup;
use App\Models\EventManager\Groups\BlockedGroupRoom;
use App\Models\Order\Cart\AccommodationCart;
use MetaFramework\Traits\Ajax;
use Throwable;

class Accommodation
{

    use Ajax;

    public function __construct()
    {
        $this->enableAjaxMode();
        $this->fetchInput();
    }

    public function deleteRoom(int $id): array
    {
        $this->response['callback'] = 'ajaxPostDeleteGroup';

        try {
            $totalBookingsCount = AccommodationCart::where('room_id', $id)->count();

            if ($totalBookingsCount) {
                $this->responseWarning(trans_choice('errors.entity_has_bookings', $totalBookingsCount, ['entity' => 'cete chambre', 'total' => $totalBookingsCount]));
            } else {
                Room::where('id', $id)->delete();
            }
            $this->responseSuccess("La chambre a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function deleteGroup(int $id): array
    {
        $this->response['callback'] = 'ajaxPostDeleteGroup';

        try {
            $roomGroup          = RoomGroup::where('id', $id)->first();
            $totalBookingsCount = $roomGroup->bookings->count();

            if ($totalBookingsCount) {
                $this->responseWarning(trans_choice('errors.entity_has_bookings', $totalBookingsCount, ['entity' => 'cete catégorie', 'total' => $totalBookingsCount]));
            } else {
                RoomGroup::where('id', $id)->delete();
            }
            $this->responseSuccess("La catégorie a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function deleteContingentRow(int $id): array
    {
        $this->response['callback'] = 'ajaxPostDeleteGroup';

        try {
            $contingent         = Contingent::where('id', $id)->with('roomGroup.bookings')->first();
            $totalBookingsCount = $contingent->roomGroup->bookings->count();

            if ($totalBookingsCount) {
                $this->responseWarning(trans_choice('errors.entity_has_bookings', $totalBookingsCount, ['entity' => 'ce contingent', 'total' => $totalBookingsCount]));
            } else {
                $contingent->delete();
                $this->responseSuccess("Le contingent a été supprimé.");
            }
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function deleteBLocked(int $id): array
    {
        $this->response['callback'] = 'ajaxPostDeleteGroup';

        BlockedRoom::where('id', $id)->delete();
        // Avant la suppression était conditionné à la non-existence de résas.
        // Pourquoi chercher à savoir les résas; on efface et c'est tout...
        // $totalBookingsCount = Accommodations::getBookingsCountForRoomGroup($blockedRoom->room_group_id);

        $this->responseSuccess("La ligne a été supprimée.");


        return $this->fetchResponse();
    }

    public function deleteBLockedGroupRoom(int $id): array
    {
        $this->response['callback'] = 'ajaxPostDeleteGroup';

        try {
            $blockedGroupRoom   = BlockedGroupRoom::where('id', $id)->first();
            $totalBookingsCount = AccommodationCart::where('room_group_id', $blockedGroupRoom->room_group_id)->count();
            if ($totalBookingsCount) {
                $this->responseWarning(trans_choice('errors.entity_has_bookings', $totalBookingsCount, ['entity' => 'cete catégorie', 'total' => $totalBookingsCount]));
            } else {
                $blockedGroupRoom->delete();
            }
            $this->responseSuccess("La ligne a été supprimée.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

    public function deleteGrant(int $id): array
    {
        $this->response['callback'] = 'ajaxPostDeleteGrant';
        try {
            // TODO a vérifie comment contrôler l'usage à la suppression
            //$grant = Grant::where('id', $id)->first();

            Grant::where('id', $id)->delete();
            $this->responseSuccess("La ligne a été supprimé.");
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }

}
