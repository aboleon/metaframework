<?php

namespace App\Actions\Front;

use App\Accessors\Front\FrontCache;
use App\Accessors\Front\FrontCartAccessor;
use App\Accessors\Front\FrontGroupCartAccessor;
use App\Interfaces\PaymentProviderInterface;
use App\Models\FrontTransaction;
use App\Models\PaymentCall;
use App\Services\PaymentProvider\PayBox\TransactionRequest;

class Transaction
{

    public static function createPaymentCall(
        FrontCartAccessor $cartAccessor,
        PaymentProviderInterface $paymentProvider,
    ): PaymentCall {
        $cart = $cartAccessor->getCart();

        // Reset
        PaymentCall::where([
            'cart_id'   => $cart->id,
            'closed_at' => null,
            'order_id'  => null,
        ])->delete();

        return PaymentCall::create(

            [
                'provider' => $paymentProvider->providerId(),
                'cart_id'  => $cart->id,
                'total'    => $cartAccessor->getPayableAmount(),

            ],
        );
    }

    public static function createCustomPaymentCall(
        PaymentProviderInterface $paymentProvider,
        string $shoppableType,
        int $shoppableId,
        int $total,
    ): PaymentCall {
        // Reset
        PaymentCall::where([
            'closed_at'      => null,
            'shoppable_type' => $shoppableType,
            'shoppable_id'   => $shoppableId,
        ])->delete();

        return PaymentCall::create(
            [
                'provider'       => $paymentProvider->providerId(),
                'shoppable_type' => $shoppableType,
                'shoppable_id'   => $shoppableId,
                'total'          => $total,

            ],
        );
    }

    public static function createReimbursmentCall(
        \App\Models\Order $order,
        PaymentProviderInterface $paymentProvider,
    ): PaymentCall {
        // Reset
        PaymentCall::where([
            'cart_id'   => null,
            'closed_at' => null,
            'order_id'  => $order->id,
        ])->delete();

        return PaymentCall::create(
            [
                'provider' => $paymentProvider->providerId(),
                'order_id' => $order->id,
                'total'    => $order->total_net + $order->total_vat,

            ],
        );
    }

    public static function createGroupCartPaymentCall(
        FrontGroupCartAccessor $cartAccessor,
        PaymentProviderInterface $paymentProvider,
    ): PaymentCall {
        // Reset
        PaymentCall::where([
            'group_manager_id' => FrontCache::getGroupManager()->id,
            'closed_at'        => null,
            'order_id'         => null,
        ])->delete();

        return PaymentCall::firstOrCreate(
            [
                'group_manager_id' => FrontCache::getGroupManager()->id,
                'closed_at'        => null,
                'order_id'         => null,
            ],
            [
                'provider'         => $paymentProvider->providerId(),
                'group_manager_id' => FrontCache::getGroupManager()->id,
                'total'            => $cartAccessor->getPayableAmount(),

            ],
        );
    }

    public static function storeTransactionResponse(PaymentCall $paymentCall): FrontTransaction
    {
        return $paymentCall->transaction()->save(new FrontTransaction([
            'transaction_id'      => TransactionRequest::transactionId(),
            'transaction_call_id' => TransactionRequest::callId(),
            'return_code'         => TransactionRequest::returnCode(),
            'details'             => json_encode(request()->all()),
        ]));
    }
}
