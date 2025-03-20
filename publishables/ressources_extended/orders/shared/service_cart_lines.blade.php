<table class="table mt-3">
    <thead>
    <tr>
        <th style="width: 40%">Prestation</th>
        <th style="width:100px">Quantité</th>
        <th>Prix Unit</th>
        <th>Prix Total</th>
        <th>Prix HT</th>
        <th>TVA</th>
        <th></th>
    </tr>
    </thead>
    <tbody id="service-cart" data-shoppable="{{ \App\Models\EventManager\Sellable::class }}">
    @php
        $services = $event->sellableService->load('event.services');
    @endphp
    @if ($error && old('shopping_cart_service'))
        @for($i=0;$i<count(old('shopping_cart_service')['id']);++$i)
            <x-old-order-service-row :services="$services"
                                     :cart="old('shopping_cart_service')"
                                     :iteration="$i"
                                     :pec_enabled="old('shopping_cart_service')['pec_enabled'][$i]"/>
        @endfor
    @else
        @if($orderAccessor->isOrder() && $orderAccessor->serviceCart())
            @foreach($orderAccessor->serviceCart() as $shoppable)
                <x-order-service-row :cart="$shoppable"
                                     :services="$services"
                                     :invoiced="(bool)$invoiced"
                                     :pecbooked="$event_contact['pec_bookings'] ?? []"/>
            @endforeach
        @endif
    @endif
    </tbody>


    @php
        $totals = $orderAccessor->isOrder() ? $orderAccessor->serviceCartTotals() : [];
    @endphp
    <tfoot id="service-total">
    <tr>
        <th colspan="3"></th>
        <th class="total text-center">{{ $totals ? $totals['total_net'] + $totals['total_vat'] : 0 }}</th>
        <th class="subtotal_ht text-center">{{ $totals ? $totals['total_net'] : 0 }}</th>
        <th class="subtotal_vat text-center">{{ $totals ? $totals['total_vat'] : 0 }}</th>
        <th></th>
    </tr>
    </tfoot>
</table>

<div id="service_cart_messages" data-ajax="{{ route('ajax') }}"></div>

<template id="service_cart_template">
    <x-order-service-row :cart="new \App\Models\Order\Cart\ServiceCart()"/>
</template>
