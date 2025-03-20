<tr style="">
    <td style="text-align: left;padding-left: 20px; {{ $style }}">
        <b>{{ $title }}</b><br>
        {{ $printableDate }} -
        {{ ($hotels[$cart->event_hotel_id] ?? '') }}<br>
        {{ $cart->id ? \App\Accessors\Dictionnaries::entry('type_chambres',  $cart->room->room_id)->name . ' x ' .$cart->room->capacity : 'NC' }}
        / {{ $cart->id ? ($room_groups[$cart->room->room_group_id] ?? 'Inconnue') : '' }}


    </td>
    <td>
        <span style=" {{ $style }}">
        {{ $cart->quantity }}
    </span>
    </td>
    <td>
        <span style=" {{ $style }}">
            @if ($isamended)
                -
            @endif
            {{ $orderAccessor->isOrator() ? '0.00' : number_format($cart->total_net, 2) }}
    </span>
    </td>
    <td>
        <span style=" {{ $style }}">
        {{ $orderAccessor->isOrator() ? '0.00' : \MetaFramework\Accessors\VatAccessor::readableArrayList()[$cart->vat_id] }}
        </span>
    </td>
    <td>
        <span style=" {{ $style }}">
            @if ($isamended)
                -
            @endif
            {{ $orderAccessor->isOrator() ? '0.00' : number_format($cart->total_net + $cart->total_vat, 2) }}
        </span>
    </td>
</tr>
@if ($amendedcart)
    <x-invoice-row-accommodation :cart="$amendedcart"
                                 :order-accessor="$orderAccessor"
                                 :hotels="$hotels" style="opacity:0.5"
                                 title="En remplacement de"
                                 :isamended="true"/>
@endif
