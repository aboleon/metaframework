<?php

namespace App\Actions\Account;

use App\Enum\SavedSearches;
use App\Helpers\AdvancedSearch\ContactAdvancedSearchHelper;
use App\Helpers\CsvHelper;
use App\Models\EventContact;
use MetaFramework\Traits\Responses;
use Throwable;

class UpdateAccountProfileAction
{
    use Responses;

    public function updateAccountProfiles(): array
    {
        $userIds = CsvHelper::csvToUniqueArray(request('ids'));
        return $this->updateAccountProfilesByUserIds($userIds);
    }

    public function updateAccountProfilesByEventContacts(): array
    {
        $eventContactIds = CsvHelper::csvToUniqueArray(request('ids'));
        $event_id = (int)request('event_id');
        $userIds = [];

        // Tous
        if (!$eventContactIds) {
            // sur event_id
            if ($event_id && !request('key') != 'participation_type_id') {
                $userIds = EventContact::where('event_id', request('event_id'))->pluck('user_id')->toArray();
            }
        } else {
        // Sélection
            $userIds = EventContact::whereIn('id', $eventContactIds)
                ->pluck('user_id')
                ->toArray();
        }

        return $this->updateAccountProfilesByUserIds($userIds);
    }

    //--------------------------------------------
    //
    //--------------------------------------------

    private function updateAccountProfilesByUserIds(array $userIds): array
    {
        $this->enableAjaxMode();
        $key = (string)request('key');

        try {
            $mode = request('mode');

            if ($mode == 'all') {
                $searchFilters = session('savedSearch.' . SavedSearches::EVENT_CONTACTS->value . '.filters');
                if ($searchFilters) {
                    $userIds = ContactAdvancedSearchHelper::getUserIdsBySearchFilters($searchFilters);
                }
            }

            switch($key) {

                case 'participation_type_id':

                    $participation_type_id = (int)request('participation_type_id');
                    $event_id = (int)request('event_id');

                    if (!$participation_type_id) {
                        $this->responseWarning("Vous n'avez sélectionné aucun type de participation");
                        return $this->fetchResponse();
                    }
                    if (!$event_id) {
                        $this->responseWarning("Le type de participation peut être changé uniquement en association avec un évènement");
                        return $this->fetchResponse();
                    }

                    $query = EventContact::query()->where(function ($where) use($event_id, $userIds, $mode) {
                         $where->where('event_id', $event_id);
                         if ($mode != 'all') {
                             $where->whereIn('user_id', $userIds);
                         }
                    });

                    $query->update(['participation_type_id' => $participation_type_id]);

                    $this->responseSuccess("Le champ <strong>Le type de participation</strong> a été mis à jour pour les entrées sélectionnées.");
                    break;

                default:
                    $this->pushMessages(
                        (new ProfileActions())
                            ->enableAjaxMode()
                            ->updateProfile($userIds, $key, request('value'))
                    );
            }


            $this->responseElement('callback', 'redrawDataTable');
        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this->fetchResponse();
    }
}
