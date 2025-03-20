<?php

namespace App\Actions\Account\Search;

use App\Accessors\Accounts;
use MetaFramework\Traits\Responses;

class Select2Accounts
{

    use Responses;

    public function filterAccounts(?string $q): array
    {
        $this->response['results'] = Accounts::filter($q)
            ->get()
            ->toArray();

        return $this->fetchResponse();
    }
}