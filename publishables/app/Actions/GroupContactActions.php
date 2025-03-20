<?php

namespace App\Actions;

use App\Models\Account;
use App\Models\Group;
use App\Models\GroupContact;
use MetaFramework\Traits\Responses;

class GroupContactActions
{

    use Responses;

    private Account $account;
    private Group $group;

    public function __construct(int $account_id, int $group_id)
    {
        try {
            $this->account = Account::findOrFail($account_id);
            $this->group = Group::findOrFail($group_id);
        } catch (\Throwable $e) {
            $this->responseException($e, "Le compte ou le groupe ne peuvent pas être retrouvés à partir de ces identifiants");
        }
    }

    public function associate(): static
    {
        try {
            $gc = GroupContact::with("group")->where('user_id', $this->account->id)->get();
            if ($gc->count() > 0) {
                $groupNames = $gc->map(function ($item) {
                    return $item->group?->name;
                })->toArray();
                $this->responseError("Le contact est déjà associé aux groupes suivants: " . implode(", ", $groupNames) . ".
                <br> Veuillez dissocier le contact de ces groupes avant de l'associer à un autre groupe.");
            } else {
                GroupContact::create([
                    'user_id' => $this->account->id,
                    'group_id' => $this->group->id
                ]);
                $this->responseSuccess("Le contact a été associé au groupe.");
            }
        } catch (\Throwable $e) {
            $this->responseException($e);
            $this->responseException($e, "Une erreur est survenue à l'association du contact au groupe.");
        }
        return $this;

    }

    public function dissociate(): static
    {
        try {
            GroupContact::where([
                'user_id' => $this->account->id,
                'group_id' => $this->group->id
            ])->delete();
            $this->responseSuccess("Le contact a été dissocié du groupe.");
        } catch (\Throwable $e) {
            $this->responseException($e);
            $this->responseException($e, "Une erreur est survenue à la dissociation du contact du groupe.");
        }
        return $this;
    }
}
