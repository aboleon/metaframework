<?php


namespace App\Actions\Account;

use App\Actions\MessageActionBase;
use App\Models\AccountProfile;
use Illuminate\Support\Facades\DB;
use MetaFramework\Traits\Ajax;
use Throwable;

class ProfileActions extends MessageActionBase
{
    use Ajax;

    public function updateProfile(array $userIds, string $key, int|string $value): self
    {

        $label = __('account.profile.' . $key);

        if (!in_array($key, (new AccountProfile())->getFillable())) {
            $this->responseError("Le champ <strong>" . $key . "</strong> est inconnu dans le Profil des comptes");
            return $this;
        }

        DB::beginTransaction();

        try {

            $query = AccountProfile::query();
            if ($userIds) {
                $query->whereIn('user_id', $userIds);
            }
            $query->update([$key => $value]);

            $this->responseSuccess("Le champ <strong>" . $label . "</strong> a été mis à jour pour les entrées sélectionnées.");

        } catch (Throwable $e) {
            $this->responseException($e);
        }

        DB::commit();

        return $this;
    }

}
