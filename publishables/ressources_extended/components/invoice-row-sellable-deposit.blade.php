@php
    $record = $cart instanceof \App\Models\Order\EventDeposit ? $cart : $cart->deposit;
    $title = 'Caution pour "' . $record->shoppable_label . '"';
    $net = $record->total_net;
    $vat = $record->total_vat;
    $vatRate = "0%";
    if ($record->vat_id){
        $vatRate = \MetaFramework\Accessors\VatAccessor::readableArrayList()[$record->vat_id];
    }
@endphp
<tr>
    <td style="text-align: left;padding-left: 20px">
        <b>{{ $title }}</b>
    </td>
    <td>{{$record->quantity ?: 1}}</td>
    @if(!$isReceipt)
    <td>
        {{ number_format($net, 2) }}
    </td>
    <td>
        {{ $vatRate }}
    </td>
    @endif
    <td>{{ number_format($net + $vat, 2) }}</td>
</tr>
