<?php

namespace App\Actions\Front;

use App\Accessors\Front\FrontCache;
use App\Accessors\Front\FrontCartAccessor;
use App\Actions\Front\Traits\FrontOrder;
use App\Actions\Order\PecActionsFront;
use App\Enum\OrderAmendedType;
use App\Enum\OrderClientType;
use App\Enum\OrderOrigin;
use App\Enum\OrderStatus;
use App\Models\EventContact;
use App\Models\FrontCart;
use App\Models\PaymentCall;
use App\Models\PecDistribution;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFramework\Accessors\VatAccessor;
use MetaFramework\Traits\Responses;

use function Symfony\Component\String\u;

class IndividualOrder
{
    private \App\Models\Order $order;
    private FrontCart $cart;
    private EventContact $eventContact;

    use FrontOrder;
    use Responses;

    public function __construct(
        public FrontCartAccessor $cartAccessor,
        public PaymentCall $paymentCall,
    ) {
        $this->cartAccessor->setCart($this->paymentCall->cart);
        $this->cart         = $this->cartAccessor->getCart();
        $this->eventContact = FrontCache::getEventContactById($this->cart->event_contact_id);
        $this->pec          = (new PecActionsFront());
    }

    public function getEventContact(): EventContact
    {
        return $this->eventContact;
    }

    public function getCartAccessor(): FrontCartAccessor
    {
        return $this->cartAccessor;
    }

    public function getCart(): FrontCart
    {
        return $this->cart;
    }


    public function createOrder(): void
    {
        Log::info('Store Individual order');

        $amendable = $this->calculateAmendableAmount();


        $totalTtc = $this->cartAccessor->getTotalTtc();
        $totalNet = $this->cartAccessor->getTotalNet();

        $this->order = \App\Models\Order::query()->create([
            'uuid'             => Str::uuid(),
            'event_id'         => $this->eventContact->event_id,
            'client_id'        => $this->eventContact->user_id,
            'client_type'      => OrderClientType::CONTACT->value,
            'total_net'        => $totalNet,
            'total_vat'        => $totalTtc - $totalNet,
            'total_pec'        => $this->cart->pec_eligible ? $this->cartAccessor->getTotalPec() : 0,
            'status'           => OrderStatus::PAID->value,
            'created_by'       => $this->eventContact->user_id,
            'origin'           => OrderOrigin::FRONT->value,
            'amend_type'       => $amendable['amendableType'],
            'amended_order_id' => $amendable['amendableOrderId'],
        ]);

        $this->cart->update([
            'order_id' => $this->order->id,
            'amended_cart_id' => $amendable['amendableType'] == OrderAmendedType::CART->value ? $amendable['amendableId'] : null
        ]);

        \App\Models\Order::where('id', $amendable['amendableOrderId'])
            ->update([
                'amend_type'          => $amendable['amendableType'],
                'amended_by_order_id' => $this->order->id,
            ]);


        $this->dispatchOrderCarts();
    }

    private function calculateAmendableAmount(): array
    {
        $amendableAmount    = 0;
        $amendableAmountNet = 0;
        $amendableOrderId   = null;
        $amendableId        = null;
        $amendableType      = null;

        $stayLines = $this->cartAccessor->getStayLines();
        if ($stayLines->isNotEmpty()) {
            foreach ($stayLines as $cartLine) {
                $amendableAmount    += $cartLine->meta_info['amendable_amount'] ?? 0;
                $amendableAmountNet += VatAccessor::netPriceFromVatPrice(
                    $cartLine->meta_info['amendable_amount'] ?? 0,
                    $cartLine->meta_info['vat_id'] ?? VatAccessor::defaultRate()['id'],
                );

                // Assign the first found values for amendable_order_id, amendable_id, and amendable (amendableType)

                $amendableOrderId = $cartLine->meta_info['amendable_order_id'] ?? null;
                $amendableId      = $cartLine->meta_info['amendable_id'] ?? null;
                $amendableType    = $cartLine->meta_info['amendable'] ?? null;
            }
        }

        return [
            'amendableAmount'    => $amendableAmount,
            'amendableAmountNet' => $amendableAmountNet,
            'amendableOrderId'   => $amendableOrderId,
            'amendableId'        => $amendableId,
            'amendableType'      => $amendableType,
        ];
    }

    public function processPec(): void
    {
        Log::info('Proces PEC');

        if ($this->cart->pec_eligible) {
            PecDistribution::where('front_cart_id', $this->cart->id)->update([
                'order_id' => $this->order->id,
            ]);
        }
    }


}
