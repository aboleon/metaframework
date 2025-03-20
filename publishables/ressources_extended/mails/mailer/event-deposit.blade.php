@php

    use MetaFramework\Accessors\Prices;
    $mail = [
                'fr' => "Bonjour ".$mailed->accessor->accountNames()."<br><br>Afin de bénéficier de la prise en charge dans le cade de l'évènement".$mailed->accessor->eventName()." vous devez payez une caution d'un montant de ".Prices::readableFormat($mailed->accessor->amount(), showDecimals: false)."<br><br>? Vous pouvez effectuer le paiement en suivant ce lien <a href='".$mailed->accessor->paymentLink()."'>".$mailed->accessor->paymentLink()."</a><br><br>L'équipe Divine ID",
                'en' => "Hello ".$mailed->accessor->accountNames()."<br><br>In order to benefit from the coverage for the event ".$mailed->accessor->eventName().", you need to pay a deposit of ".Prices::readableFormat($mailed->accessor->amount(), showDecimals: false)."<br><br>You can make the payment by following this link <a href='".$mailed->accessor->paymentLink()."'>".$mailed->accessor->paymentLink()."</a><br><br>The Divine ID team",
            ];

            echo $mail[$mailed->locale];

@endphp
