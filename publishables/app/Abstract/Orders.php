<?php

namespace App\Abstract;

use App\Enum\OrderClientType;
use App\Enum\OrderOrigin;
use App\Enum\OrderStatus;
use App\Models\Event;
use App\Models\Group;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use MetaFramework\Traits\Ajax;
use MetaFramework\Traits\Responses;

abstract class Orders
{
    use Ajax;
    use Responses;

    protected ?Event $event = null;
    protected null|int|Group $group = null;
    protected ?Collection $orders = null;
    protected ?string $account_type = null;
    protected array $filters = [];
    protected array $relations = [];

    public function getOrders(): Collection
    {
        if ($this->orders !== null) {
            return $this->orders;
        }

        if ($this->event) {
            $this->filters['event_id'] = $this->event->id;
        }

        $this->orders = Order::query()
            ->filters($this->filters)
            ->withRelations($this->relations)
            ->get();

        return $this->orders;
    }

    public function resetOrders(): self
    {
        $this->orders = null;

        return $this;
    }

    public function setClientType(string $type): self
    {
        $this->account_type = $type;

        $this->filters['client_type'] = $type;

        return $this;
    }

    public function setEvent(int|Event $event): self
    {
        $this->event = is_int($event) ? Event::find($event) : $event;

        if ($this->event) {
            $this->filters['event_id'] = $this->event->id;
        }

        return $this;
    }

    public function setGroup(int|Group $group): self
    {
        $this->group = is_int($group) ? Group::find($group) : $group;

        if ($this->group) {
            $this->filters['client_type'] = OrderClientType::GROUP->value;
            $this->filters['client_id']   = $this->group->id;
        }

        return $this;
    }

    public function setOrigin(string $origin): self
    {
        if (in_array($origin, OrderOrigin::values())) {
            $this->filters['origin'] = $origin;
        }

        return $this;
    }

    public function unpaid(): self
    {
        $this->filters['status'] = OrderStatus::UNPAID->value;

        return $this;
    }

    public function paid(): self
    {
        $this->filters['status'] = OrderStatus::PAID->value;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function with(array $relations): self
    {
        $this->relations = $relations;
        return $this;
    }
}
