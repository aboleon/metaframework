<?php

namespace App\Actions\Front;

use App\Accessors\Front\FrontCartAccessor;
use App\Accessors\Front\FrontGroupCartAccessor;
use App\Accessors\Order\Orders;
use App\Actions\Front\Traits\FrontOrder;
use App\Enum\EventDepositStatus;
use App\Enum\OrderOrigin;
use App\Enum\OrderStatus;
use App\Models\Order\EventDeposit;
use App\Models\PaymentCall;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class Order
{
    use FrontOrder;

    public function __construct(
        protected PaymentCall $paymentCall,
    ) {}

    /**
     * @throws Exception
     */
    public function processGroupManagerOrder(): void
    {
        Log::info('Start processing front group order');

        $groupCartAccessor = new FrontGroupCartAccessor();
        $carts             = $groupCartAccessor->getCarts();


        // Transforme en commande
        $orderAction = (new GroupManagerOrder(
            groupCartAccessor: $groupCartAccessor,
            paymentCall: $this->paymentCall,
            transaction: $this->paymentCall->transaction,
        )
        );

        $orderAction->createMainOrder();


        try {
            foreach ($carts as $cart) {
                Log::info('SubOrder for cart #'.$cart->id);

                $frontCartAccessor = new FrontCartAccessor();
                $frontCartAccessor->setCart($cart);

                $orderAction->createSubOrder($cart);
                //$orderAction->notifyBeneficiary();

                # Delete stock
                $frontCartAccessor->getCartLines()->each(fn($item) => $item->tempStock()->delete());

                Log::info('End processing front group order');
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }


        $this->order        = $orderAction->getMainOrder();
        $this->eventContact = $orderAction->getEventContact();

        $this->updateOrderKeys($this->paymentCall);
        $this->setInvoiceable();
        $this->setPayment($this->paymentCall);
        $this->attachInvoice();
    }

    /**
     * @throws Exception
     */
    public function processIndividualOrder(): void
    {
        Log::info('Start Individual order');

        $orderAction = (new IndividualOrder(
            new FrontCartAccessor(),
            $this->paymentCall,
        ));

        # Check if it is Grant Deposit
        if ($orderAction->cartAccessor->getGrantWaiverFeeLines()->isNotEmpty()) {
            $this->updatePendingGrantDeposit($orderAction);
                Log::info('Order orderContainsGrantDeposit');
                // TODO: ERROR: View [mails.mailer.grant-deposit-accepted-notification] not found
                /*
                $mc = new MailController();
                $mc->ajaxMode()->distribute(
                    'GrantDepositAcceptedNotification',
                    $orderAction->getEventContact(),
                )->fetchResponse();
                */
        } else {
            # Regular Order
            $orderAction->createOrder();
            $this->order        = $orderAction->getOrder();
            $this->eventContact = $orderAction->getEventContact();

            $orderAction->processPec();
            $this->setInvoiceable();
            $this->attachInvoice();

            Log::info('Deleting temp stock');
            $orderAction->getCartAccessor()->getCartLines()->each(fn($item) => $item->tempStock()->delete());
        }


        $this->updateOrderKeys($this->paymentCall);
        $this->setPayment($this->paymentCall);
    }

    private function updatePendingGrantDeposit(IndividualOrder $orderAction): self
    {
        $cart        = $orderAction->cartAccessor->getGrantWaiverFeeLines()->first();
        $transaction = $orderAction->getCart();

        $deposit = EventDeposit::find($cart->shoppable_id);

        $deposit->update([
            'status'           => EventDepositStatus::PAID->value,
            'paybox_num_trans' => $transaction->num_trans, // TODO A supprimer
            'paybox_num_appel' => $transaction->num_appel, // TODO A supprimer
        ]);

        $orderAction->cartAccessor->getGrantWaiverFeeLines()->each(fn($item) => $item->delete());

        $this->order = $deposit->order;

        $this->order->status = OrderStatus::PAID->value;
        $this->order->origin = OrderOrigin::FRONT->value;
        $this->order->save();

        $eventContact = $orderAction->getEventContact();
        $eventContact->pec_enabled = true;
        $eventContact->save();

        return $this;
    }

}
