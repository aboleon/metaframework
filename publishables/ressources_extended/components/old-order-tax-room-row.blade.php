<tr class="order-taxroom-row" data-identifier="{{ $identifier }}"
    data-amount="{{ $cart['amount'][$i] }}"
    data-amount-net="{{ \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart['amount'][$i], $cart['vat_id'][$i]) }}"
    data-amount-vat="{{ \MetaFramework\Accessors\VatAccessor::vatForPrice($cart['amount'][$i], $cart['vat_id'][$i] }}"
    data-cart-id>
    <td class="date"></td>
    <td class="room label"
        data-label="{{ $cart['text_hotel_name'][$i] . $cart['text_room_label'][$i] . ' / '.$cart['text_room_category'][$i] }}">
        <b class="text-danger">Frais de dossier</b><br>
        <span class="hotel-name d-block">{!! $cart['text_hotel_name'][$i] !!}</span>
        <span class="main fw-bold">{{ $cart['text_room_label'][$i] }}</span>
        <span class="text-secondary category"> / {{  $cart['text_room_category'][$i] }}</span>
        <input type="hidden" class="room_id"
               name="shopping_cart_taxroom[room_id][]"/>
        <input type="hidden"
               class="pec_enabled"
               name="shopping_cart_taxroom[pec_enabled][]"
               value="{{  $cart['pec_enabled'][$i] }}"/>
    </td>
    <td class="pec-mark align-middle">{!! $cart['pec_enabled'][$i] ? '<span class="ms-2 fw-bold"><i class="bi bi-check-circle-fill text-success"></i> PEC</span>' : '' !!}</td>
    <td class="quantity">
        <x-mfw::number name="shopping_cart_taxroom.quantity."
                       :value="$cart['quantity'][$i]"/>
    </td>
    <td class="amount">
        <x-mfw::number name="shopping_cart_taxroom.amount."
                       class="text-end" :value="$cart['amount'][$i]" :readonly="true"/>
    </td>
    <td class="amount_total">
        <x-mfw::number name="shopping_cart_taxroom.amount_total."
                       class="text-end" :value="$cart['amount_total']" :readonly="true"/>
    </td>
    <td class="amount_net">
        <x-mfw::number name="shopping_cart_taxroom.amount_net."
                       class="text-end" :value="$cart['amount_net']" :readonly="true"/>
    </td>
    <td class="amount_vat">
        <x-mfw::number name="shopping_cart_taxroom.amount_vat."
                       class="text-end" :value="$cart['amount_vat']" :readonly="true"/>
    </td>
    <td>
        <x-mfw::input type="hidden" class="event_hotel_id" :value="$cart['event_hotel_id']"
                      name="shopping_cart_taxroom.event_hotel_id."/>
        <x-mfw::input type="hidden" class="vat_id" :value="$cart['vat_id']"
                      name="shopping_cart_taxroom.vat_id."/>

        <x-mfw::simple-modal id="delete_order_taxroom_row"
                             class="btn mfw-bg-red btn-sm mt-2 invoiced {{ $invoiced ? 'd-none' : '' }}"
                             title="Suppression d'un élement de frais de dossier"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeTaxRoomRow"
                             :identifier="$identifier"
                             text='<i class="fas fa-trash"></i>'/>
    </td>
</tr>
