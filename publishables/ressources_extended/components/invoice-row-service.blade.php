@php
    $pec = $cart->total_pec;
@endphp
<tr>
    <td style="text-align: left;padding-left: 20px">
        <span class="main">{{  $sellable?->title ?? 'NC' }} - {{ $group?->name }}</span>
    </td>
    <td>{{ $cart->quantity }}</td>
    <td>
        @if($pec > 0)
            0
        @else
            {{ $orderAccessor->isOrator() ? '0.00' : number_format(\MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($cart->unit_price, $cart->vat_id), 2) }}
        @endif
    </td>
    <td>
        {{ \MetaFramework\Accessors\VatAccessor::readableArrayList()[$cart->vat_id] }}
    </td>
    <td>{{ $orderAccessor->isOrator() ? '0.00' : number_format($cart->total_net + $cart->total_vat, 2) }}</td>
</tr>
