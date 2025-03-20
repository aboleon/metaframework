@if(isset($orderAccessor))
    @if($orderAccessor->serviceCart())
        @foreach($orderAccessor->serviceCart() as $shoppable)
            <x-invoice-row-service :cart="$shoppable"
                                   :order-accessor="$orderAccessor"
                                   :services="$services"/>
        @endforeach
    @endif
    @if($orderAccessor->accommodationCart())
        @foreach($orderAccessor->accommodationCart() as $shoppable)
            <x-invoice-row-accommodation :cart="$shoppable"
                                         :order-accessor="$orderAccessor"
                                         :hotels="$hotels"
                                         :amendedorder="$amendedOrder"/>
        @endforeach
        @if ($amendedOrder && $amendedOrder->amend_type == \App\Enum\OrderAmendedType::ORDER->value)
                    @foreach($amendedOrder->accommodation as $cartCollection)
                        <x-invoice-row-accommodation :cart="$cartCollection"
                                                     :order-accessor="$orderAccessor"
                                                     :hotels="$hotels"
                                                     style="opacity:0.5"
                                                     :title="$loop->first ? 'En remplacement de' : ''"
                                                     :isamended="true"/>
                    @endforeach

            @endif
    @endif
    @if($orderAccessor->grantDepositCart())
        @foreach($orderAccessor->grantDepositCart() as $shoppable)
            <x-invoice-row-grant-deposit :cart="$shoppable" :isReceipt="$isReceipt"/>
        @endforeach
    @endif
    @if($orderAccessor->sellableDepositCart())
        @foreach($orderAccessor->sellableDepositCart() as $shoppable)
            <x-invoice-row-sellable-deposit :cart="$shoppable" :isReceipt="$isReceipt"/>
        @endforeach
    @endif
    @if($orderAccessor->taxRoomCart())
        @foreach($orderAccessor->taxRoomCart() as $shoppable)
            <x-invoice-row-taxcart :cart="$shoppable" :hotels="$hotels"/>
        @endforeach
    @endif
@endif
