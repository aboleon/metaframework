<?php

namespace App\Actions\EventManager;

use App\Http\Requests\EventManager\GrantDepositRequest;
use App\Models\Event;
use App\Models\EventManager\Grant\GrantDepositLocation;
use MetaFramework\Traits\Ajax;

class GrantDeposit
{
    use Ajax;

    public function __construct(
        private readonly Event               $event,
        private readonly GrantDepositRequest $request,
    )
    {
    }

    public function __invoke(): void
    {

        $this->manageGrantBindedDeposit();
        $this->manageGrantBindedParticipations();
    }

    private function manageGrantBindedDeposit(): void
    {

        $data = [];

        if ($this->request->filled('grant_binded_location')) {

            $input = $this->request->get('grant_binded_location');

            for ($i = 0; $i < count($input['country_code']); ++$i) {
                if (empty($input['id'][$i])) {
                    $data[] = new GrantDepositLocation([
                        'country_code' => $input['country_code'][$i],
                        'locality' => $input['locality'][$i],
                    ]);
                }
            }
        }

        $this->event->grantDeposit->locations()->saveMany($data);


    }

    private function manageGrantBindedParticipations(): void
    {
        $this->event->grantDeposit->participations()->sync((array)$this->request->get('grant_participation_types'));
    }

}
