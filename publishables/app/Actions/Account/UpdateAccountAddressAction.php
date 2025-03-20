<?php

namespace App\Actions\Account;

use App\Enum\OrderClientType;
use App\Models\AccountAddress;
use App\Models\Group;
use App\Models\GroupAddress;
use MetaFramework\Traits\Responses;
use Throwable;

class UpdateAccountAddressAction
{
    use Responses;

    protected AccountAddress|GroupAddress|null $address = null;

    public function __construct(public array $data)
    {
        $this->getAccount();
    }

    public function getAccount(): AccountAddress|GroupAddress|null
    {
        try {
            $this->address = match($this->data['account_type']) {
                OrderClientType::GROUP->value => GroupAddress::findOrFail($this->data['address_id']),
                default => AccountAddress::findOrFail($this->data['address_id']),
            };
        } catch (Throwable $e) {
            $this->responseException($e, "Aucune adresse compte ne peut être récupéré avec id " . ($this->data['address_id'] ?? 'NC'));
        }

        return $this->address;
    }

    public function update(): self
    {
        if (!$this->address) {

            if (isset($this->data['account_type']) && $this->data['account_type'] == OrderClientType::GROUP->value) {
                $this->address = new GroupAddress();
                $this->address->group_id = $this->data['account_id'];
            } else {
                $this->address = new AccountAddress();
                $this->address->user_id = $this->data['account_id'];
            }

            $this->address->name = 'Facturation commande';
            $this->address->billing = 1;
            $this->address->save();
        }

        $company = $this->data['company'] ?? null;
        if ($this->data['account_type'] == OrderClientType::CONTACT->value) {
            $this->address->company = $company;
        } else {
            Group::where('id', $this->data['account_id'])->update(['company' => $company]);
        }

        $this->address->update($this->data);

        $this->responseSuccess("L'adresse a été mise à jour");

        return $this;


    }

    public function getAddressId(): ?int
    {
        return $this->address->id;
    }
}
