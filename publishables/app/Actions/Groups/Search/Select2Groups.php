<?php

namespace App\Actions\Groups\Search;

use App\Accessors\Accounts;
use App\Accessors\GroupAccessor;
use MetaFramework\Traits\Responses;

class Select2Groups
{

    use Responses;

    public function filter(?string $q): array
    {
        $this->response['results'] = GroupAccessor::filter($q)
            ->get()
            ->toArray();

        return $this->fetchResponse();
    }
}
