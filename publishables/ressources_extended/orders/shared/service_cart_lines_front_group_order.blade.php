<table class="table mt-3">
    <thead>
    <tr>
        <th>Affectation</th>
        <th>Prestation</th>
        <th class="text-center">Quantité</th>
        <th class="text-end">Prix Unit</th>
        <th class="text-end">Prix Total</th>
        <th class="text-end">Prix HT</th>
        <th class="text-end">TVA</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @php
        $cartSellableCache = [];
        $cartSellableGroupCache = [];
        $subcart = ['net' => 0, 'vat' => 0];
    @endphp
    @foreach($serviceSuborders as $suborder)
        @foreach($suborder->services as $cart)
            @php
                if (!isset($cartSellableCache[$cart->service_id])) {
                    $cartSellableCache[$cart->service_id] = $services->where('id', $cart->service_id)->first();
                }
                $cartSellable = $cartSellableCache[$cart->service_id];

                if ($cartSellable && !isset($cartSellableGroupCache[$cartSellable->service_group])) {
                    $cartSellableGroupCache[$cartSellable->service_group] = $cartSellable->event->services->where('id', $cartSellable->service_group)->first();
                }
                $cartSellableGroup = $cartSellableGroupCache[$cartSellable->service_group] ?? null;
                $subcart['net'] += $cart->total_net;
                $subcart['vat'] += $cart->total_vat;
            @endphp

            <tr data-cart-id="{{ $cart->id }}">
                <td>{{ $suborder->account->names() }}</td>
                <td>
                    {{  $cartSellable?->title ?? 'NC' }} - {{ $cartSellableGroup?->name }}
                    <x-cancellation-request :cart="$cart" />
                    <x-order-item-cancelled :cart="$cart"/>

                </td>
                <td class="text-center">{{ $cart->quantity }}</td>
                <td class="text-end">{{ $cart->unit_price }}</td>
                <td class="text-end">{{ $cart->total_net + $cart->total_vat }}</td>
                <td class="text-end">{{ $cart->total_net }}</td>
                <td class="text-end">{{ $cart->total_vat }}</td>
                <td class="text-center">
                    <ul class="mfw-actions">
                        <x-mfw::edit-link
                            :route="route('panel.manager.event.orders.edit', ['event' => $order->event_id, 'order' => $cart->order_id])"/>
                    </ul>
                </td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="4"></th>
        <th class="text-end">{{ $subcart['net'] + $subcart['vat']  }}</th>
        <th class="text-end">{{ $subcart['net'] }}</th>
        <th class="text-end">{{ $subcart['vat'] }}</th>
        <th></th>
    </tr>
    </tfoot>
</table>
