<?php

namespace App\Actions\Front\Paybox;

use App\Accessors\OrderAccessor;
use App\Actions\Ajax\AjaxAction;
use App\Enum\OrderOrigin;
use App\Enum\OrderType;
use App\Models\CustomPaymentCall;
use App\Models\EventContact;
use App\Models\Order;
use App\Services\PaymentProvider\PayBox\Paybox;
use Illuminate\Support\Str;

class PayboxActions extends AjaxAction
{

    public function getPayboxFormByOrder()
    {
        return $this->handle(function () {
            [$orderId, $eventContactId] = $this->checkRequestParams(['order_id', 'event_contact_id']);

            $order = Order::find($orderId);
            if ( ! $order) {
                $this->responseError("La commande n'existe pas.");

                return;
            }

            $eventContact = EventContact::find($eventContactId);
            if ( ! $eventContact) {
                $this->responseError("Le contact n'existe pas.");
                return;
            }

            if ($order->origin == OrderOrigin::FRONT->value) {
                $paymentCall = $order->paymentCall;
            } else {
                $paymentCall = match ($order->type) {
                    OrderType::GRANTDEPOSIT->value => $order->grantDepositRecord->paymentCall,
                    default => null
                };
            }

            # Payer une commande faite en BO
            # ------------------------------
            if (!$paymentCall && OrderOrigin::default()) {

                $orderAccessor = (new OrderAccessor($order));
                $order->customPaymentCall()->delete();

                // Create Deposit Payment Call
                $paymentCall = new CustomPaymentCall([
                    'uuid'     => (string)Str::uuid(),
                    'provider' => (new Paybox())->signature()['id'],
                    'total'    => $orderAccessor->calculateRemainingAmountToPay(),
                ]);
                $order->customPaymentCall()->save($paymentCall);
            }

            if ( ! $paymentCall) {
                $this->responseError(__('errors.impossible_identify_payment_call'));
                return;
            }

            $paybox = (new Paybox())
                ->setOrderable($paymentCall)
                ->fetchAvailableServer();

            $this->responseElement('payboxFormBegin', $paybox->renderPaymentFormBegin());
            $this->responseElement('payboxServerOk', (bool)$paybox->getPaymentServer());
            $this->responseElement('payboxFormEnd', $paybox->renderPaymentFormEnd());
        });
    }
}
