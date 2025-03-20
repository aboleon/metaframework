@php
    use App\Models\Order\Cart\GrantDepositCart;

    $title = "Caution grant " . $cart->grant->title;
    $net = $cart->total_net;
    $vat = $cart->total_vat;
    $vatRate = "0%";
    if ($cart->vat_id){
        $vatRate = \MetaFramework\Accessors\VatAccessor::readableArrayList()[$cart->vat_id];
    }
@endphp

<tr>
    <td style="text-align: left;padding-left: 20px">
        <b>{{ $title }}</b>
    </td>
    <td>{{$cart->quantity}}</td>
    @if (!$isReceipt)
        <td>
            {{ number_format($net, 2) }}
        </td>
        <td>
            {{ $vatRate }}
        </td>
    @endif
    <td>{{ number_format($net + $vat, 2) }}</td>
</tr>
