<?php

namespace App\Services\PaymentProvider\PayBox;

/**
 * Example
 * /paybox/receiver/effectue?order_uuid=2d564bd3-7568-4736-ada7-07e3d916aa6a&t=79270533&a=XXXXXX&c=MasterCard&e=00000&q=14%3A32%3A28&s=38115077
 * [order_uuid] => 441f1571-49fd-465a-9eae-af9dd118e7a6
 * [t] => 79263147
 * [a] => XXXXXX // ? card number...?
 * [c] => CB
 * [e] => 00000
 * [q] => 17:54:55
 * [s] => 38114140
 */
class TransactionRequest
{

    /**
     * @return string|null
     */
    public static function returnCode(): ?string
    {
        return request('e');
    }

    /**
     * @return string|null
     */
    public static function uuid(): ?string
    {
        return request('uuid');
    }

    public static function transactionId(): ?string
    {
        return request('s');
    }

    public static function callId(): ?string
    {
        return request('t');
    }

    public static function time(): ?string
    {
        return request('q');
    }

    public static function paymentMean(): ?string
    {
        return request('c');
    }

    public static function successful(): bool
    {
        $returnCode = trim((string)self::returnCode());

        return $returnCode !== '' && intval($returnCode) === 0;
    }

    // Savoir si l'URL appélée est un retour de PayBox
    public static function isInAction(): bool
    {
        return request()->has('t') && request()->has('s') && request()->has('e');
    }
}
