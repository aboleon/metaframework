@php use App\Models\EventContact; @endphp

@if ($orderAccessor->isNotFrontGroupOrder())
    @php
        // event_contact_id à supprimer de chez les carts

        $serviceAttributions = $orderAccessor->serviceAttributions();
        $accommodationAttributions = $orderAccessor->accommodationAttributions();

        $eventContactIds = $serviceAttributions->pluck('event_contact_id')->merge(collect($accommodationAttributions)->pluck('event_contact_id'))->unique();
        $eventContacts = collect();
        if ($eventContactIds->isNotEmpty()) {
            $eventContacts = EventContact::with('account')->findMany($eventContactIds);
        }

    @endphp


    @foreach($orderAccessor->serviceCart() as $shoppable)
        <x-invoice-row-service :cart="$shoppable" :services="$services"/>
    @endforeach
    @foreach($orderAccessor->accommodationCart() as $shoppable)
        <x-invoice-row-accommodation :cart="$shoppable" :hotels="$hotels"/>
    @endforeach

@else

    {{-- Recap pour les paniers achetés en front depuis un group manager --}}
    @php
        $suborders = $order->suborders->load('services','accommodation','account','taxRoom');
        $taxRoomSuborders = $order->suborders->load('accommodation','account');
    @endphp
    @if ($suborders->contains(function ($suborder) {
            return $suborder->services->isNotEmpty();
        }))

        @foreach($suborders as $suborder)
            @foreach($suborder->services as $cart)
                <x-invoice-row-service :cart="$cart" :services="$services"/>
            @endforeach
        @endforeach
    @endif
    @if ($suborders->contains(function ($suborder) {
            return $suborder->accommodation->isNotEmpty();
        }))

        @foreach($suborders as $suborder)
            @foreach($suborder->accommodation as $cart)
                <x-invoice-row-accommodation :cart="$cart" :hotels="$hotels"/>
            @endforeach
        @endforeach
    @endif
    @if ($suborders->contains(function ($suborder) {
            return $suborder->taxRoom->isNotEmpty();
        }))

        @foreach($suborders as $suborder)
            @foreach($suborder->taxRoom as $cart)
                <x-invoice-row-taxcart :cart="$cart" :hotels="$hotels"/>
            @endforeach
        @endforeach
    @endif
@endif
