<?php

namespace App\Interfaces;


use App\Models\CustomPaymentCall;
use App\Models\EventContact;
use Illuminate\Contracts\Support\Renderable;

interface CustomPaymentInterface
{

    # Le formulaire à afficher
    public function renderCustomPaymentForm(): Renderable|string;

    # Les messages en fonction du statut de paiement
    public function paymentStateMessage(): string;

    # Actions lorsqu'un paiement est réussi
    public function processSuccessCustomPayment();

    /* Le mail d'appel de paiement
     * Doit retourner un array compatible avec MetaFramework\Traits\Responses fetchResponse() method
     */
    public function sendPaymentMail(CustomPaymentCall $paymentCall);

    public function sendPaymentResponseNotification(CustomPaymentCall $paymentCall): array;

    public function getEventContact(): EventContact;

}
