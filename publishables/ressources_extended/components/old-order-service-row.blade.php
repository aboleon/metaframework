<tr class="order-service-row" data-identifier="{{ $identifier }}" data-cart-id="{{ $cart->id ?? null }}">
    <td class="label">
        <span class="main">{{  $sellable?->title ?? 'NC' }} - {{ $group?->name }}</span>
        <input type="hidden" class="service_id" name="shopping_cart_service[id][]" value="{{ $cart['id'][$iteration] }}"/>
        <small class="d-block text-danger d-none error"></small>
    </td>
    <td class="quantity">
        <x-mfw::number name="shopping_cart_service.quantity." class="qty" :params="['data-qty' => $qty]" :value="$qty"/>
    </td>
    <td class="price" data-unit-price="{{ $cart['unit_price'][$iteration] }}"
        data-price-net="{{ \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart['unit_price'][$iteration], $cart['vat'][$iteration]) }}"
        data-vat="{{ \MetaFramework\Accessors\VatAccessor::vatForPrice($cart['unit_price'][$iteration], $cart['vat'][$iteration]) }}">
        <x-mfw::number name="shopping_cart_service.unit_price."
                       class="text-end"
                       :value="$cart['unit_price'][$iteration]"
                       :readonly="true"/>
    </td>
    <td class="price_total">
        <x-mfw::number name="shopping_cart_service.price." class="text-end" value="0" :readonly="true"/>
    </td>
    <td class="price_ht">
        <x-mfw::number name="shopping_cart_service.price_ht." class="text-end" value="0" :readonly="true"/>
    </td>
    <td class="vat">
        <x-mfw::number name="shopping_cart_service.vat." class="text-end" value="0" :readonly="true"/>
    </td>
    <td>
        <x-mfw::input type="hidden" class="vat_id" :value="$cart['vat_id'][$iteration]"
                      name="shopping_cart_service.vat_id."/>d
        <x-mfw::simple-modal id="delete_order_service_row"
                             class="btn btn-danger btn-sm mt-2"
                             title="Suppression d'un élement de commande"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeServiceRow"
                             :identifier="$identifier"
                             text="Supprimer"/>
    </td>
</tr>
