<tr>
    <td style="text-align: left;padding-left: 20px">
        <b>Frais de dossier Hébergement</b><br>
        {{ ($hotels[$cart->event_hotel_id] ?? '') }}<br>
        {{ $cart->id ? \App\Accessors\Dictionnaries::entry('type_chambres',  $cart->room->room_id)->name . ' x ' .$cart->room->capacity : 'NC' }}
        / {{ $cart->id ? ($room_groups[$cart->room->room_group_id] ?? 'Inconnue') : '' }}
    </td>
    <td>1</td>
    <td>
        {{ number_format($cart->amount_net, 2) }}
    </td>
    <td>
        {{ \MetaFramework\Accessors\VatAccessor::readableArrayList()[$cart->vat_id] }}
    </td>
    <td>{{ number_format($cart->amount_net + $cart->amount_vat, 2) }}</td>
</tr>
