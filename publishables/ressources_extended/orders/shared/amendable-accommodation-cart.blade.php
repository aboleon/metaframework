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
    <tbody id="accommodation-cart"
           data-shoppable="App\Models\EventManager\Accommodation\RoomGroup"
           data-cart-type="{{ \App\Enum\OrderCartType::ACCOMMODATION->value }}">
    @if ($edit_amended)
        @foreach($orderAccessor->accommodationCart() as $shoppable)
            <x-order-accommodation-row :cart="$shoppable"
                                       :order="$order"
                                       :event="$event"
                                       :dates="$dates"
                                       :hotels="$hotels"
                                       :invoiced="(bool)$invoiced"/>
        @endforeach
    @endif
    </tbody>
    <tfoot id="accommodation-total-amendable">
    <tr class="opacity-50" id="amendable-base-price">
        <th colspan="5" data-title="Prix de base">{{ $edit_amended ? 'Prix de base' : '' }}</th>
        <th class="total text-center">{{ $edit_amended ? \MetaFramework\Accessors\Prices::readableFormat($orderAccessor->accommodationCart()->sum('unit_price'), '') : '' }}</th>
        <th class="subtotal_ht text-center">{{ $edit_amended ? \MetaFramework\Accessors\Prices::readableFormat($orderAccessor->accommodationCart()->sum('total_net'), '') : '' }}</th>
        <th class="subtotal_vat text-center">{{ $edit_amended ? \MetaFramework\Accessors\Prices::readableFormat($orderAccessor->accommodationCart()->sum('total_vat'), '') : '' }}</th>
        <th></th>
    </tr>
    <tr id="amendable-supplement">
        <th colspan="5"
            data-title="Supplément">{{ $edit_amended ? 'Supplément' : '' }}</th>
        <th class="total_amended text-center">{{ $edit_amended ? \MetaFramework\Accessors\Prices::readableFormat($order->total_net + $order->total_vat, '') : '' }}</th>
        <th class="subtotal_ht_amended text-center">{{ $edit_amended ? \MetaFramework\Accessors\Prices::readableFormat($order->total_net, '') : '' }}</th>
        <th class="subtotal_vat_amended text-center">{{ $edit_amended ? \MetaFramework\Accessors\Prices::readableFormat($order->total_vat, '') : '' }}</th>
        <th></th>
    </tr>
    </tfoot>
</table>

@if ($edit_amended)
    <input type="hidden" name="amended[total_net]" value="{{  $order->total_net }}"/>
    <input type="hidden" name="amended[total_vat]" value="{{  $order->total_vat }}"/>
    <input type="hidden" name="amended[total_pec]" value="{{  $order->total_pec }}"/>
@endif

<h4 class="mt-4 fs-3 fw-bold">La réservation d'origine</h4>
