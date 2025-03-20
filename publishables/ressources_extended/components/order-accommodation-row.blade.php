<tr class="order-accommodation-row {{ ($cart->id ? $cart->date->format('Y-m-d').'-'. $cart->room_id : '')
. ($amendablemode ? (!$amendable ? ' opacity-50' : ' amendable') : '') }}"
    data-identifier="{{ $identifier }}"
    data-pec-enabled="{{ $cart->total_pec ? 1 : 0 }}"
    data-cart-id="{{ $cart->id }}">
    <td class="date"
        data-date="{{ $cart->id ? $cart->date->format('Y-m-d') : '' }}"
        data-readable-date="{{ $cart->id ? $printableDate : '' }}">
        <span>{{ $cart->id && $printDate ? $printableDate : '' }}</span>
        <x-mfw::input type="hidden"
                      :value="$cart->id ? $cart->date->format('Y-m-d') : ''"
                      name="shopping_cart_accommodation.date."/>
    </td>
    <td class="room label"
        data-label="{{ $cart->id ? str_replace(['hors ligne','en ligne'], '', strip_tags($hotels[$cart->event_hotel_id],'span')) . $room_label . ' / '.$room_category  : '' }}"
        data-room-id="{{ $cart->id ? $cart->room_id : '' }}"
        data-capacity="{{ $capacity }}">
        <span class="hotel d-block">{!! $cart->id ? ($hotels[$cart->event_hotel_id] ?? '') : '' !!}</span>
        <span
            class="main fw-bold">{{ $cart->id ? $room_label : 'NC' }}</span>
        <span
            class="text-secondary category"> / {{ $cart->id ? $room_category : '' }}</span>
        <span class="d-block text-danger"></span>
        <input type="hidden" class="room_id"
               name="shopping_cart_accommodation[room_id][]"
               value="{{ $cart->id ? $cart->room_id : '' }}"/>
        <input type="hidden" class="room_group_id"
               name="shopping_cart_accommodation[room_group_id][]"
               value="{{  $cart->id ? $cart->room->room_group_id : '' }}"/>
        <input type="hidden"
               class="pec_enabled"
               name="shopping_cart_accommodation[pec_enabled][]"
               value="{{  $cart->total_pec ? 1 : 0 }}"/>
        @if(!$invoiced && $lockQuantity)
            <small class="text-danger">Modification verrouillée en raison des attributions</small>
        @endif
    </td>
    <td class="align-middle text-end">
        <span class="d-block pec-mark">
            {!! $cart->total_pec ? '<span class="ms-2 fw-bold"><i class="bi bi-check-circle-fill text-success"></i> PEC</span>' : '' !!}
        </span>
        <span class="d-block quota-mark">
            @if($cart->id && $cart->on_quota)
                <span class="ms-2 fw-bold d-block"><i class="bi bi-check-circle-fill text-success"></i> Quota</span>
                <small
                    class="d-block text-secondary text-nowrap">Attributions {{ $attributed .'/'.$cart->quantity }}</small>
            @endif
        </span>
    </td>
    <td class="quantity">
        <x-mfw::number name="shopping_cart_accommodation.quantity."
                       class="qty" :value="$cart->id ? $cart->computedQuantity() : 1"
                       :params="['data-qty' => $cart->id ? $cart->computedQuantity() : 1, 'data-onquota' => (int)$cart->on_quota, 'data-to-attribute' => $to_attribute]"
                       :readonly="$invoiced or $lockQuantity"/>

        @if($cart->id && $cart->computedQuantity() != $cart->quantity)
            <small class="text-dark d-block mt-1">A l'origine - {{ $cart->quantity }}</small>
        @endif
    </td>
    <td class="price"
        data-unit-price="{{ $cart->id ? $cart->unit_price : 0 }}"
        data-price-net="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart->unit_price, $cart->vat_id) : 0 }}"
        data-vat="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::vatForPrice($cart->unit_price, $cart->vat_id) : 0 }}"
        data-unit-price-pec="{{ $pec_price_net + $pec_price_vat }}"
        data-price-net-pec="{{ $pec_price_net }}"
        data-vat-pec="{{ $pec_price_vat }}">
        <x-mfw::number name="shopping_cart_accommodation.unit_price."
                       class="text-end"
                       :value="$cart->id ? $cart->unit_price : null"
                       :readonly="true"/>
    </td>
    <td class="price_total">
        <x-mfw::number name="shopping_cart_accommodation.price."
                       class="text-end"
                       :value="$cart->id ? $cart->total_net + $cart->total_vat : null"
                       :readonly="true"/>
    </td>
    <td class="price_ht">
        <x-mfw::number name="shopping_cart_accommodation.price_ht."
                       class="text-end"
                       :value="$cart->id ? $cart->total_net : null"
                       :readonly="true"/>
    </td>
    <td class="vat">
        <x-mfw::number name="shopping_cart_accommodation.vat."
                       class="text-end"
                       :value="$cart->id ? $cart->total_vat : 0"
                       :readonly="true"/>
    </td>
    <td>
        <x-mfw::input type="hidden"
                      class="event_hotel_id"
                      :value="$cart->id ? $cart->event_hotel_id : ''"
                      name="shopping_cart_accommodation.event_hotel_id."/>
        <x-mfw::input type="hidden" class="vat_id" :value="$cart->id ? $cart->vat_id : ''"
                      name="shopping_cart_accommodation.vat_id."/>
        <x-mfw::input type="hidden" class="pec_allocation_ht" :value="$pec_allocation_net"
                      name="shopping_cart_accommodation.pec_allocation_ht."/>
        <x-mfw::input type="hidden" class="pec_allocation_vat" :value="$pec_allocation_vat"
                      name="shopping_cart_accommodation.pec_allocation_vat."/>
        <x-mfw::input type="hidden" class="on_quota" :value="(int)$cart->on_quota"
                      name="shopping_cart_accommodation.on_quota."/>

        @if (!$cart->wasAmended && !$amendablemode && $attributed == 0 )
            <x-mfw::simple-modal id="delete_order_accommodation_row"
                                 class="btn mfw-bg-red btn-sm mt-2 invoiced {{ $invoiced ? 'd-none' : '' }}"
                                 title="Suppression d'un élement de commande"
                                 confirmclass="btn-danger"
                                 confirm="Supprimer"
                                 callback="removeAccommodationRow"
                                 :identifier="$identifier"
                                 :modelid="$cart->id"
                                 text='<i class="fas fa-trash"></i>'/>
        @endif

        @if (!$invoiced && $attributed > 0)
            <small class="text-danger">suppression désactivée<br>attributions en place</small>
        @endif

        @if ($invoiced && !$amendablemode)

            @if ($cart->wasAmended)

                <a href="{{ route('panel.manager.event.orders.edit', [$event->id, $cart->wasAmended->id]) }}"
                   class="btn btn-secondary btn-sm mt-2">Modifiée par la commande #{{ $cart->wasAmended->id }}</a>
            @else
                @if ($canAmendOrCancel)
                    <x-mfw::simple-modal id="cancel_order_accommodation_row"
                                         class="btn btn-yellow btn-sm mt-2 cancel-btn {{ $invoiced && $cart->computedQuantity() > 0 ? '' : 'd-none' }}"
                                         title="Annulation d'une chambre"
                                         confirmclass="btn-danger"
                                         confirm="Annulation"
                                         onshow="appendSelectableToAccommodationCancellation"
                                         callback="cancelAccommodationRow"
                                         :identifier="$identifier"
                                         :modelid="$cart->id"
                                         text="Annuler"/>
                @endif
                {{-- Bouton Modifier retiré le 13 Janv 2025
                    @if ($canAmendOrCancel && !$cart->order->amended_order_id && $cart->computedQuantity() > 0)
                        <a href="{{ route('panel.manager.event.orders.accommodation.amend', [$event->id, $order->id, $cart->id]) }}"
                           class="btn btn-dark btn-sm mt-2">Modifier</a>
                    @endif
                 --}}
            @endif
        @endif

        <x-cancellation-request :cart="$cart"/>

        <x-order-item-cancelled :cart="$cart"/>
    </td>
</tr>
