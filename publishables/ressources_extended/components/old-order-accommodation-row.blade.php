<tr class="order-accommodation-row" data-identifier="{{ $identifier }}">
    <td class="date" data-date="{{ '['.$date.']' }}"
        data-readable-date="{{ '['.$printableDate.']' }}">
        <span>{{ $iteration < 1 ? $printableDate : '' }}</span>
        <x-mfw::input type="hidden" :value="$date"
                      name="shopping_cart_accommodation{{ '['.$date.']' }}.date."/>
    </td>
    <td class="room label">
        data-label="{{ str_replace(['hors ligne','en ligne'], '', strip_tags($hotel,'span')) . $room_label . ' / '.$room_category }}"
        data-room-id="{{ $cart->id ? $cart->room_id : '' }}"
        data-capacity="{{ $capacity }}"
        <span class="hotel d-block">{!! $hotel !!}</span>
        <span class="main fw-bold">{{ $room_label . ' x ' . $quantity}}</span>
        <span class="text-secondary category"> / {{ $room_category }}</span>
        <span class="d-block text-danger"></span>
        <input type="hidden" class="room_id"
               name="shopping_cart_accommodation{{ '['.$date.']' }}[room_id][]"
               value="{{ $cart['room_id'][$iteration] }}"/>
        <input type="hidden" name="shopping_cart_accommodation{{ '['.$date.']' }}[room_group_id][]"
               value="{{ $cart['room_group_id'][$iteration] }}"/>
    </td>
    <td class="quantity">
        <x-mfw::number name="shopping_cart_accommodation{{ '['.$date.']' }}.quantity."
                       class="qty" :value="$quantity"/>
    </td>
    <td class="price" data-unit-price="{{ $cart['unit_price'][$iteration] }}"
        data-price-net="{{ \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart['unit_price'][$iteration], 1) }}"
        {{-- 1 == $cart->vat_id --}}
        data-vat="{{ \MetaFramework\Accessors\VatAccessor::vatForPrice($cart['unit_price'][$iteration], 1) }}">
        <x-mfw::number name="shopping_cart_accommodation{{ '['.$date.']' }}.unit_price."
                       class="text-end" :value="$cart['unit_price'][$iteration]" :readonly="true"/>
    </td>
    <td class="price_total">
        <x-mfw::number name="shopping_cart_accommodation{{ '['.$date.']' }}.price."
                       class="text-end" :value="$cart['unit_price'][$iteration] * $quantity"
                       :readonly="true"/>
    </td>
    <td class="price_ht">
        <x-mfw::number name="shopping_cart_accommodation{{ '['.$date.']' }}.price_ht."
                       class="text-end" :value="$cart['price_ht'][$iteration] * $quantity" :readonly="true"/>
    </td>
    <td class="vat">
        <x-mfw::number name="shopping_cart_accommodation{{ '['.$date.']' }}.vat."
                       class="text-end" :value="$quantity * $cart['vat'][$iteration]" :readonly="true"/>
    </td>
    <td>
        <x-mfw::input type="hidden" class="event_hotel_id" :value="$cart['event_hotel_id'][$iteration]"
                      name="shopping_cart_accommodation{{ '['.$date.']' }}.event_hotel_id."/>
        <x-mfw::input type="hidden" class="vat_id" :value="$cart['vat_id'][$iteration]"
                      name="shopping_cart_accommodation{{ '['.$date.']' }}.vat_id."/>

        <x-mfw::input type="hidden" class="pec_allocation_ht" :value="$cart['pec_allocation_ht'][$iteration]"
                      name="shopping_cart_accommodation.pec_allocation_ht."/>
        <x-mfw::input type="hidden" class="pec_allocation_vat" :value="$cart['pec_allocation_vat'][$iteration]"
                      name="shopping_cart_accommodation.pec_allocation_vat."/>
        <x-mfw::input type="hidden" class="on_quota" :value="(int)$cart['on_quota'][$iteration]"
                      name="shopping_cart_accommodation.on_quota."/>

        <x-mfw::simple-modal id="delete_order_accommodation_row"
                             class="btn btn-danger btn-sm mt-2"
                             title="Suppression d'un élement de commande"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeAccommodationRow"
                             :identifier="$identifier"
                             :modelid="null"
                             text="Supprimer"/>
    </td>
</tr>
