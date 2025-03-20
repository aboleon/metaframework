<?php

namespace App\Actions\AccountProfile;

use App\Models\Account;
use App\Models\AccountProfile;
use MetaFramework\Traits\DateManipulator;
use MetaFramework\Traits\Responses;
use Throwable;

class Profile
{

    use DateManipulator;
    use Responses;

    private array $data;

    /**
     * @param array<string> $validated_data
     */
    public function __construct(
        public readonly Account $account,
        public readonly array   $validated_data
    )
    {
        $this->data = $this->validated_data;

        if ($this->validated_data['birth']) {
            $this->data['birth'] = $this->parseDate($this->validated_data['birth']);
        }
    }

    public function create(): static
    {
        $this->data['created_by'] = auth()->id();

        $this->account->profile()->save(
            new AccountProfile($this->data)
        );

        return $this;

    }

    public function update(): static
    {
        $accessor = new \App\Accessors\Accounts($this->account);

        if (!$accessor->hasAddressInFrance()) {
            $this->data['rpps'] = null;
        }

        $this->account->profile()->update($this->data);

        return $this;
    }
}
