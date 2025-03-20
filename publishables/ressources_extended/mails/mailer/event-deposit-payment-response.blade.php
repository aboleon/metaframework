<?php

use MetaFramework\Accessors\Prices;

$text = $mailed->accessor->accountNames();


$text .= match ($mailed->paymentState()) {
    \App\Enum\PaymentCallState::SUCCESS->value => " a payé sa caution d'un montant",
    default => " a essayé mais n'a pas réuissi à payer sa caution",
};


$text .= " de ".Prices::readableFormat($mailed->amount(), showDecimals: false)
    ." pour la prise en charge dans le cade de l'évènement".$mailed->accessor->eventName();


if ( ! in_array(
    $mailed->paymentState(),
    [\App\Enum\PaymentCallState::SUCCESS->value, \App\Enum\PaymentCallState::default()],
)
) {
    $text .= "<br><br>Le paiement a été ".Str::lower(\App\Enum\PaymentCallState::translated($mailed->paymentState()));
}

if ($mailed->paymentState() == \App\Enum\PaymentCallState::default()) {
    $text .= "<br><br>Le paiement est toujours ".Str::lower(
            \App\Enum\PaymentCallState::translated($mailed->paymentState()),
        );
}


echo $text;

