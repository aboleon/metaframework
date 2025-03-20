<tr class="order-service-row" data-identifier="{{ $identifier }}" data-cart-id="{{ $cart->id }}">
    <td class="label">
        <span class="main">{{  $sellable?->title ?? 'NC' }} - {{ $group?->name }}</span>
        <span class="{{ $pec_enabled ? '' : 'd-none' }} pec_label">
            <span class="ms-2 fw-bold"><i class="bi bi-check-circle-fill text-success"></i> PEC</span>
            <span class="max_pec">{!! (int)$sellable?->pec_max_pax ? '&nbsp;(max '.$sellable?->pec_max_pax.')' :''  !!}</span>
        </span>
        @if ($sellable?->id && isset($pecbooked[$sellable->id]) && $pecbooked[$sellable->id] >= (int)$sellable->pec_max_pax)
            <span class="d-block pec-maxed text-danger">PEC déjà utilisée</span>
        @endif
        <input type="hidden"
               class="service_id"
               name="shopping_cart_service[id][]"
               value="{{ $cart->id ? $cart->service_id : null }}"/>
        <small class="d-block text-danger d-none error"></small>
    </td>
    <td class="quantity">
        <x-mfw::number name="shopping_cart_service.quantity."
                       class="qty"
                       :params="['data-qty' => $qty]"
                       :value="$qty"
                       :readonly="$invoiced"/>
    </td>
    <td class="price" data-unit-price="{{ $cart->id ? $cart->unit_price : 0 }}"
        data-price-net="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart->unit_price, $cart->vat_id) : 0 }}"
        data-vat="{{ $cart->id ? \MetaFramework\Accessors\VatAccessor::vatForPrice($cart->unit_price, $cart->vat_id) : 0 }}">

        <x-mfw::number name="shopping_cart_service.unit_price." class="text-end"
                       :value="$cart->id ? $cart->unit_price : null" :readonly="true"/>
    </td>
    <td class="price_total">
        <x-mfw::number name="shopping_cart_service.price."
                       class="text-end"
                       :value="$cart->id ? $cart->total_net + $cart->total_vat : null"
                       :readonly="true"/>
    </td>
    <td class="price_ht">
        <x-mfw::number name="shopping_cart_service.price_ht." class="text-end"
                       :value="$cart->id ? $cart->total_net : null" :readonly="true"/>
    </td>
    <td class="vat">
        <x-mfw::number name="shopping_cart_service.vat."
                       class="text-end"
                       :value="$cart->id ? $cart->total_vat : 1"
                       :readonly="true"/>
    </td>
    <td>
        <x-mfw::input type="hidden" class="vat_id" :value="$cart->id ? $cart->vat_id : ''"
                      name="shopping_cart_service.vat_id."/>


        <x-mfw::simple-modal id="delete_order_service_row"
                             class="btn btn-danger btn-sm mt-2 invoiced {{ $invoiced ? 'd-none' : '' }}"
                             title="Suppression d'un élement de commande"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeServiceRow"
                             :identifier="$identifier"
                             :modelid="$cart->id"
                             text="Supprimer"/>

        <x-cancellation-request :cart="$cart"/>
        <x-order-item-cancelled :cart="$cart"/>

        <x-mfw::simple-modal id="cancel_order_service_row"
                             class="btn btn-yellow btn-sm mt-2 cancel-btn {{ $invoiced && !$cart->cancelled_at? '' : 'd-none' }}"
                             title="Annulation d'un élement de commande"
                             confirmclass="btn-danger"
                             confirm="Annulation"
                             callback="cancelServiceRow"
                             :identifier="$identifier"
                             :modelid="$cart->id"
                             text="Annuler"/>

        <x-mfw::input type="hidden" class="pec_enabled" name="shopping_cart_service.pec_enabled."
                      :value="$pec_enabled"/>
        <x-mfw::input type="hidden" class="pec_max" name="shopping_cart_service.pec_max."
                      :value="(int)$sellable?->pec_max_pax"/>
    </td>
</tr>
