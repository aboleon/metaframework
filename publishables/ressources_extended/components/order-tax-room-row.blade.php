<tr class="order-taxroom-row {{ $cart->id ?'room-'. $cart->room_id : '' }}"
    data-identifier="{{ $identifier }}"
    data-amount="{{ $cart->amount ?? 0 }}"
    data-amount-net="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart->amount, $cart->vat_id) : 0 }}"
    data-amount-vat="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::vatForPrice($cart->amount, $cart->vat_id) : 0 }}"
    data-cart-id="{{ $cart->id }}">
    <td class="date"></td>
    <td class="room label"
        data-label="{{ $cart->id ? str_replace(['hors ligne','en ligne'], '', strip_tags($hotels[$cart->event_hotel_id],'span')) . $room_label . ' / '.$room_category  : '' }}">
        <b class="text-danger">Frais de dossier</b><br>
        <span class="hotel-name d-block">{!! $cart->id ? ($hotels[$cart->event_hotel_id] ?? '') : '' !!}</span>
        <span class="main fw-bold">{{ $cart->id ? $room_label : 'NC' }}</span>
        <span class="text-secondary category"> / {{ $cart->id ? $room_category : '' }}</span>
        <input type="hidden" class="room_id"
               name="shopping_cart_taxroom[room_id][]"
               value="{{ $cart->id ? $cart->room_id : '' }}"/>
        <input type="hidden"
               class="pec_enabled"
               name="shopping_cart_taxroom[pec_enabled][]"
               value="{{  $cart->amount_pec ? 1 : 0 }}" />
        <input type="hidden"
               class="text_hotel_name"
               name="shopping_cart_taxroom[text_hotel_name][]"
               value="{!! $cart->id ? (str_replace(['hors ligne','en ligne'], '', strip_tags($hotels[$cart->event_hotel_id],'span')) ?? '') : '' !!}" />
        <input type="hidden"
               class="text_room_label"
               name="shopping_cart_taxroom[text_room_label][]"
               value="{!! $cart->id ? $room_label : '' !!}" />
        <input type="hidden"
               class="text_room_category"
               name="shopping_cart_taxroom[text_room_category][]"
               value="{!! $cart->id ? $room_category : '' !!}" />
    </td>
    <td class="pec-mark align-middle">{!! $cart->total_pec ? '<span class="ms-2 fw-bold"><i class="bi bi-check-circle-fill text-success"></i> PEC</span>' : '' !!}</td>
    <td class="quantity">
        <x-mfw::number name="shopping_cart_taxroom.quantity."
                       :value="$cart->id ? $cart->quantity : 1"/>
    </td>
    <td class="price"
        data-unit-price="{{ $cart->id ? $cart->amount : 0 }}"
        data-price-net="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart->amount, $cart->vat_id) : 0 }}"
        data-vat="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::vatForPrice($cart->amount, $cart->vat_id) : 0 }}"
        data-unit-price-pec="{{ $pec_price_net + $pec_price_vat }}"
        data-price-net-pec="{{ $pec_price_net }}"
        data-vat-pec="{{ $pec_price_vat }}">
        <x-mfw::number name="shopping_cart_taxroom.unit_price."
                       class="text-end" :value="$cart->id ? $cart->amount : null" :readonly="true"/>
    </td>
    <td class="amount_total">
        <x-mfw::number name="shopping_cart_taxroom.price."
                       class="text-end" :value="$cart->id ? $cart->amount_net + $cart->amount_vat  : 0" :readonly="true"/>
    </td>
    <td class="amount_net">
        <x-mfw::number name="shopping_cart_taxroom.price_ht."
                       class="text-end" :value="$cart->id ? $cart->amount_net : null" :readonly="true"/>
    </td>
    <td class="amount_vat">
        <x-mfw::number name="shopping_cart_taxroom.vat."
                       class="text-end" :value="$cart->id ? $cart->amount_vat : 0" :readonly="true"/>
    </td>
    <td>
        <x-mfw::input type="hidden" class="event_hotel_id" :value="$cart->id ? $cart->event_hotel_id : ''"
                      name="shopping_cart_taxroom.event_hotel_id."/>
        <x-mfw::input type="hidden" class="vat_id" :value="$cart->id ? $cart->vat_id : ''"
                      name="shopping_cart_taxroom.vat_id."/>

        <x-mfw::input type="hidden" class="pec_allocation_ht" :value="$pec_allocation_net"
                      name="shopping_cart_taxroom.pec_allocation_ht."/>
        <x-mfw::input type="hidden" class="pec_allocation_vat" :value="$pec_allocation_vat"
                      name="shopping_cart_taxroom.pec_allocation_vat."/>

        <x-mfw::simple-modal id="delete_order_taxroom_row"
                             class="btn mfw-bg-red btn-sm mt-2 invoiced {{ $invoiced ? 'd-none' : '' }}"
                             title="Suppression d'un élement de frais de dossier"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeTaxRoomRow"
                             :identifier="$identifier"
                             :modelid="$cart->id"
                             text='<i class="fas fa-trash"></i>' />
    </td>
</tr>
