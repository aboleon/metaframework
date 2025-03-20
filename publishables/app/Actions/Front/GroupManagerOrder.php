<?php

namespace App\Actions\Front;

use App\Accessors\Front\FrontCache;
use App\Accessors\Front\FrontCartAccessor;
use App\Accessors\Front\FrontGroupCartAccessor;
use App\Actions\Front\Traits\FrontOrder;
use App\Actions\Order\PecActionsFront;
use App\Enum\OrderClientType;
use App\Enum\OrderOrigin;
use App\Enum\OrderStatus;
use App\Http\Controllers\MailController;
use App\Models\EventContact;
use App\Models\EventManager\EventGroup;
use App\Models\FrontCart;
use App\Models\FrontTransaction;
use App\Models\PaymentCall;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GroupManagerOrder
{
    protected ?PecActionsFront $pec;
    protected FrontCart $cart;
    protected ?EventGroup $group;
    private \App\Models\Order $mainOrder;

    use FrontOrder;

    public function __construct(
        public FrontGroupCartAccessor $groupCartAccessor,
        public PaymentCall $paymentCall,
        public FrontTransaction $transaction,
    ) {
        $this->group = $this->groupCartAccessor->getGroup();
    }

    public function getEventContact(): ?EventContact
    {
        return $this->groupCartAccessor->getGroupManager();
    }

    /**
     * @throws Exception
     */
    public function createSubOrder(FrontCart $cart): self
    {
        $this->cart = $cart;
        $this->createFromCart();
        $this->dispatchOrderCarts();

        return $this;
    }

    private function createFromCart(): void
    {
        $this->eventContact = $this->cart->eventContact->load('event', 'user');
        $this->cartAccessor = (new FrontCartAccessor())->setCart($this->cart);


        $totalTtc = $this->cartAccessor->getTotalTtc();
        $totalNet = $this->cartAccessor->getTotalNet();

        $this->order = \App\Models\Order::query()->create([
            'uuid'        => Str::uuid(),
            'event_id'    => $this->eventContact->event_id,
            'client_id'   => $this->eventContact->user_id,
            'client_type' => OrderClientType::CONTACT->value,
            'total_net'   => $totalNet,
            'total_vat'   => $totalTtc - $totalNet,
            'total_pec'   => 0,
            'status'      => OrderStatus::PAID->value,
            'created_by'  => $this->cart->groupManager?->user_id ?: $this->eventContact->user_id,
            'origin'      => OrderOrigin::FRONT->value,
            'parent_id'   => $this->mainOrder->id,
        ]);

        $this->cart->update(['order_id' => $this->order->id]);
    }

    public function createMainOrder(): \App\Models\Order
    {
        $totalNet = $this->groupCartAccessor->getTotalNet();

        $this->mainOrder = \App\Models\Order::query()->create([
            'uuid'        => Str::uuid(),
            'event_id'    => FrontCache::getEvent()->id,
            'client_id'   => $this->group->group->id,
            'client_type' => OrderClientType::GROUP->value,
            'total_net'   => $totalNet,
            'total_vat'   => $this->groupCartAccessor->getTotalTtc() - $totalNet,
            'total_pec'   => 0,
            'status'      => OrderStatus::PAID->value,
            'created_by'  => $this->groupCartAccessor->getGroupManager()?->user_id ?: $this->eventContact->user_id,
            'origin'      => OrderOrigin::FRONT->value,
        ]);

        return $this->mainOrder;
    }

    public function notifyBeneficiary(): void
    {
        try {
            (new MailController())
                ->ajaxMode()
                ->distribute('GroupManagerBoughtThingsForYouNotification', $this->cart->event_contact_id.'-'.$this->cart->group_manager_event_contact_id)->fetchResponse();
        } catch (Throwable $e) {
            Log::error('Group Order: impossible to mail eventContact #'.$this->cart->event_contact_id);
            Log::error($e->getMessage());
        }
    }

    public function getMainOrder(): \App\Models\Order
    {
        return $this->mainOrder;
    }
}
