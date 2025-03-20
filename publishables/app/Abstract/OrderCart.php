<?php

namespace App\Abstract;

use App\Models\Event;
use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;
use MetaFramework\Traits\Ajax;
use MetaFramework\Traits\Responses;

abstract class OrderCart
{
    use Ajax;
    use Responses;

    protected ?Event $event = null;
    protected ?Group $group = null;
    protected ?Collection $orders = null;
    protected ?string $client_type = null;
    protected array $filters = [];

    abstract public function setCartFromId(int $cart_id);

    abstract public function updateOrderCartFromRowManipulation(array $data);
}
