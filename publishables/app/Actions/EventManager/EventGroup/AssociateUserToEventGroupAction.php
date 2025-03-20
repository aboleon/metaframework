<?php

namespace App\Actions\EventManager\EventGroup;

use App\Accessors\EventContactAccessor;
use App\Actions\Ajax\AjaxAction;
use App\Enum\RegistrationType;
use App\Http\Controllers\MailController;
use App\Models\EventContact;
use App\Models\EventManager\EventGroup;
use App\Models\User;

class AssociateUserToEventGroupAction extends AjaxAction
{

    private ?User $user = null;
    private ?EventGroup $group = null;
    private int $participation_type_id;


    public function associateUserToEventGroup(): array
    {
        return $this->handle(function () {

            $this->participation_type_id = (int)request("participation_type_id");
            $user_id = (int)request("user_id");
            $event_group_id = (int)request("event_group_id");

            if (!$this->participation_type_id) {
                $this->responseError("Veuillez renseigner le type de participation");
            }

            if (!$user_id or !$event_group_id) {
                $this->responseError("L'utilisateur ou le groupe ne sont pas spécifiés.");
                return $this->fetchResponse();
            }

            $this->findUser($user_id);
            $this->findEventGroup($event_group_id);

            if ($this->hasErrors()) {
                return $this->fetchResponse();
            }


            $ec = EventContactAccessor::getEventContactByEventAndUser($this->group->event_id, $this->user);

            if (!$ec) {
                $ec = EventContact::create([
                    'user_id' => $this->user->id,
                    'event_id' => $this->group->event_id,
                    'registration_type' => RegistrationType::GROUP_MEMBER->value,
                    'participation_type_id' => $this->participation_type_id,
                ]);
            }

            if (!$ec->participationType) {
                $ec->update([
                    'participation_type_id' => $this->participation_type_id,
                ]);
            }


            // Associer le membre au groupe
            $action = new AssociateEventContactToEventGroupAction();
            $action->associateEventContactToEventGroup($ec->id, $this->group->id);
            $this->pushMessages($action);

            if (!$action->hasErrors()) {
                // Send mail
                $mc = new MailController();
                $mc->ajaxMode()->distribute('NewGroupMemberAddedNotification', $this->user->id . ' - ' . $this->group->id)->fetchResponse();
                $this->pushMessages($mc);
            }

            return $this->fetchResponse();
        });
    }

    private function findUser($user_id): void
    {
        $this->user = User::find($user_id);

        if (!$this->user) {
            $this->responseError('User not found');
        }
    }

    private function findEventGroup($event_group_id): void
    {
        $this->group = EventGroup::find($event_group_id);

        if (!$this->group) {
            $this->responseError('Event group not found');
        }
    }

}
