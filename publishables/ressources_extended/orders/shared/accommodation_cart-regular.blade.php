@if ($orderAccessor->hasAccommodationQuota()->isNotEmpty())
    <x-warning-line class="bg-warning-subtle" warning="Cette commande contient des réservations sur quotas bloqués"/>
@endif

<table class="table mt-3">

    <thead>
    <tr>
        <th style="width: 140px">Date</th>
        <th>Chambre</th>
        <th style="width:84px"></th>
        <th style="width:100px">Quantité</th>
        <th style="width:130px">Prix Unit</th>
        <th style="width:130px">Prix Total</th>
        <th style="width:130px">Prix HT</th>
        <th style="width:130px">TVA</th>
        <th></th>
    </tr>
    </thead>
    <tbody id="accommodation-cart{{  $is_amendable ? '-original' : '' }}"
           data-shoppable="App\Models\EventManager\Accommodation\RoomGroup"
           data-cart-type="{{ \App\Enum\OrderCartType::ACCOMMODATION->value }}">

    @if($error && !$is_amendable && old('shopping_cart_accommodation'))
        @php
            $total_net = 0;
            $total_vat = 0;
        @endphp
        @foreach(old('shopping_cart_accommodation') as $date => $subset)
            @for($i=0;$i<count($subset['date']);++$i)
                <x-old-order-accommodation-row :date="$date" :cart="$subset" :hotels="$hotels" :iteration="$i"/>
                @php
                    $total_net+= $subset['unit_price'][$i] * $subset['quantity'][$i];
                    $total_vat+= $subset['vat'][$i] * $subset['quantity'][$i];
                @endphp
            @endfor
        @endforeach
        @php
            $totals = [
                'total_net' => $total_net,
                'total_vat' => $total_vat,
                'total_pec' => 0,
            ];
        @endphp
    @else

        @php
            $accommodationCart = ($is_amendable or $has_amended_cart) && $edit
            ? $order->amendedOrder->accommodation :
            ($orderAccessor->isOrder() && $orderAccessor->accommodationCart() ? $orderAccessor->accommodationCart() : collect()) ;
            $globalAmendableMode = $orderAccessor->isOrder() && $orderAccessor->wasAmendedByAnotherOrder();

            // Load cancelled carts
           // $accommodationCart->load('cancellations');

        @endphp

        @foreach($accommodationCart as $shoppable)
            @php
                $amendableMode = $globalAmendableMode ?: (
        ($order->amend_type == \App\Enum\OrderAmendedType::ORDER->value && $order->amended_order_id) ||
        (($is_amendable || $has_amended_cart) &&
        ($amended_cart ? $amended_cart->id == $shoppable->id : $amendable_cart->id == $shoppable->id))
    );
            @endphp
            <x-order-accommodation-row :cart="$shoppable"
                                       :order="$order"
                                       :event="$event"
                                       :dates="$dates"
                                       :hotels="$hotels"
                                       :attributions="$orderAccessor->accommodationAttributions()"
                                       :invoiced="(bool)$invoiced"
                                       :amendablemode="$amendableMode"/>
            @php
                $dates= [$shoppable->getRawOriginal('date')];
            @endphp
        @endforeach
    </tbody>

    <tbody id="accommodation-taxroom{{$is_amendable ? '-original' : ''}}">
    @if($error && old('shopping_cart_taxroom'))
        @php
            $total_net = 0;
            $total_vat = 0;
        @endphp
        @foreach(old('shopping_cart_taxroom') as $date => $subset)
            @for($i=0;$i<count($subset['date']);++$i)
                <x-old-order-tax-room-row :cart="$subset" :invoiced="(bool)$invoiced"
                                          :hotels="$hotels_whitout_status"/>
                @php
                    $total_net+= $subset['unit_price'][$i] * $subset['quantity'][$i];
                    $total_vat+= $subset['vat'][$i] * $subset['quantity'][$i];
                @endphp
            @endfor
        @endforeach
        @php
            $totals = [
                'total_net' => $total_net,
                'total_vat' => $total_vat,
                'total_pec' => 0,
            ];
        @endphp
    @else
        @if ($orderAccessor->isOrder() && $orderAccessor->taxRoomCart())
            @foreach($orderAccessor->taxRoomCart() as $shoppable)
                <x-order-tax-room-row :cart="$shoppable" :invoiced="(bool)$invoiced"
                                      :hotels="$hotels_whitout_status"/>
                @php
                    $dates= [$shoppable->getRawOriginal('date')];
                @endphp
            @endforeach
        @endif
    @endif
    </tbody>
    @php

        if($is_amendable && $edit) {
            $orderAccessor = (new \App\Accessors\OrderAccessor($order->amendableAccommodation->order));
        }
        if (isset($orderAccessor)) {
                $accommodationTotals = $orderAccessor->accommodationCartTotals();
                $taxRoomTotals = $orderAccessor->taxRoomCartTotals();
            }
    @endphp
    @endif

    @if($orderAccessor->hasAmendedAnotherOrder())
        @php
            $amendedOrderTotals = (new \App\Accessors\OrderAccessor($orderAccessor->getAmendedOrder()))->accommodationCartTotals();
        @endphp
    @endif

    @php
        $totals = match (true) {
        $orderAccessor->hasAmendedAnotherOrder() => [
            'total' => $amendedOrderTotals['total_net'] + $amendedOrderTotals['total_vat'],
            'subtotal_ht' => $amendedOrderTotals['total_net'],
            'subtotal_vat' => $amendedOrderTotals['total_vat']
        ],
        $orderAccessor->isOrder() && !$has_amended_cart => [
            'total' => $accommodationTotals['total_net'] + $accommodationTotals['total_vat'] + $taxRoomTotals['total_net'] + $taxRoomTotals['total_vat'],
            'subtotal_ht' => $accommodationTotals['total_net'] + $taxRoomTotals['total_net'],
            'subtotal_vat' => $accommodationTotals['total_vat'] + $taxRoomTotals['total_vat']
        ],
        $has_amended_cart => [
            'total' => $amended_cart->unit_price,
            'subtotal_ht' => $amended_cart->total_net,
            'subtotal_vat' => $amended_cart->total_vat
        ],
        default => [
            'total' => 0,
            'subtotal_ht' => 0,
            'subtotal_vat' => 0
        ]
    };

    @endphp

    <tfoot id="accommodation-total{{$is_amendable ? '-original' : ''}}">
    <tr>
        <th colspan="5"></th>
        <th class="total text-center">{{ $totals['total'] }}</th>
        <th class="subtotal_ht text-center">{{ $totals['subtotal_ht'] }}</th>
        <th class="subtotal_vat text-center">{{ $totals['subtotal_vat'] }}</th>
        <th></th>
    </tr>
    </tfoot>
</table>

<template id="accommodation_cart_template">
    <x-order-accommodation-row :cart="new \App\Models\Order\Cart\AccommodationCart()"/>
</template>

<template id="accommodation_taxroom_template">
    <x-order-tax-room-row :cart="new \App\Models\Order\Cart\TaxRoomCart()"/>
</template>

<div id="accommodation_cart_messages" data-ajax="{{ route('ajax') }}"></div>

@include('orders.shared.accompanying')
@include('orders.shared.roomnotes')

@push('callbacks')
    <script src="{{ asset('js/orders/accommodation_cart_callbacks.js') }}"></script>
@endpush
@push('js')
    @if($orderAccessor->isOrder())
        <script>$('#order-accommodation-search').find('input,select').prop('disabled', false);</script>
    @endif
    <script src="{{ asset('js/orders/accommodation_cart.js') }}"></script>
@endpush
