<?php

namespace App\Traits;

use App\Models\Account;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;

trait ModelSetters
{
    protected ?Event $event = null;
    protected ?Order $order = null;
    protected null|int|User|Account $user = null;

    public function setEvent(int|Event $event): self
    {
        $this->event = is_int($event) ? Event::find($event) : $event;

        return $this;
    }

    public function setOrder(int|Order $order): self
    {
        $this->order = is_int($order) ? Order::find($order) : $order;
        return $this;
    }

    public function setUser(null|int|User|Account $user): self
    {
        $this->user = is_int($user) ? User::find($user) : $user;
        return $this;
    }

}
