<table class="table mt-3">

    <thead>
    <tr>
        <th>Affectation</th>
        <th style="width: 140px">Date</th>
        <th>Chambre</th>
        <th style="width:100px">Quantité</th>
        <th style="width:130px" class="text-end">Prix Unit</th>
        <th style="width:130px" class="text-end">Prix Total</th>
        <th style="width:130px" class="text-end">Prix HT</th>
        <th style="width:130px" class="text-end">TVA</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @php
        use App\Accessors\Dictionnaries;
        $cartAccommodationRoomCache = [];
        $cartAccommodationCategoryCache = [];
        $cartAccommodationRoomGroupsCache = [];
        $subcart = ['net' => 0, 'vat' => 0];
    @endphp
    @foreach($accommodationSuborders as $suborder)
        @foreach($suborder->accommodation as $cart)
            @php
                if (!isset($cartAccommodationRoomCache[$cart->room->room_id])) {
                    $cartAccommodationRoomCache[$cart->room->room_id] = Dictionnaries::entry('type_chambres', $cart->room->room_id)->name . ' x ' . $cart->room->capacity;
                }


                $room_label = $cartAccommodationRoomCache[$cart->room->room_id];

                if (!isset($cartAccommodationRoomGroupsCache[$cart->eventHotel->id])) {
                    $cartAccommodationRoomGroupsCache[$cart->eventHotel->id] = $cart->eventHotel->roomGroups->pluck('name', 'id')->toArray();
                }


                if (!isset($cartAccommodationCategoryCache[$cart->room->room_group_id])) {
                    $cartAccommodationCategoryCache[$cart->room->room_group_id] = $cartAccommodationRoomGroupsCache[$cart->eventHotel->id][$cart->room->room_group_id];
                }

                $room_category = $cartAccommodationCategoryCache[$cart->room->room_group_id];

                $subcart['net'] += $cart->total_net;
                $subcart['vat'] += $cart->total_vat;

            @endphp

            <tr data-cart-id="{{ $cart->id }}">
                <td>{{ $suborder->account->names() }}</td>
                <td>{{  $cart->date->format('d/m/Y') }}</td>
                <td>
                    {{  str_replace(['hors ligne','en ligne'], '', strip_tags($hotels[$cart->event_hotel_id],'span')) . $room_label . ' / '.$room_category }}
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

    @if ($accommodationSuborders->contains(function ($suborder) {
            return $suborder->taxRoom->isNotEmpty();
        }))

        @foreach($accommodationSuborders as $suborder)
            @foreach($suborder->taxRoom as $cart)
                <tr data-cart-id="{{ $cart->id }}">
                    <td>{{ $suborder->account->names() }}</td>
                    <td>Frais de dossier</td>
                    <td>{{  str_replace(['hors ligne','en ligne'], '', strip_tags($hotels[$cart->event_hotel_id],'span')) . $room_label . ' / '.$room_category }}
                    </td>
                    <td class="text-center">{{ $cart->quantity }}</td>
                    <td class="text-end">{{ $cart->amount }}</td>
                    <td class="text-end">{{ $cart->amount }}</td>
                    <td class="text-end">{{ $cart->amount_net }}</td>
                    <td class="text-end">{{ $cart->amount_vat }}</td>
                    <td class="text-center">
                        <ul class="mfw-actions">
                            <x-mfw::edit-link
                                :route="route('panel.manager.event.orders.edit', ['event' => $order->event_id, 'order' => $cart->order_id])"/>
                        </ul>
                    </td>
                </tr>
                @php
                    $subcart['net'] += $cart->amount_net;
                    $subcart['vat'] += $cart->amount_vat;
                @endphp
            @endforeach
        @endforeach
    @endif
    </tbody>
    <tfoot>
    <tr>
        <th colspan="5"></th>
        <th class="text-end">{{ $subcart['net'] + $subcart['vat']  }}</th>
        <th class="text-end">{{ $subcart['net'] }}</th>
        <th class="text-end">{{ $subcart['vat'] }}</th>
        <th></th>
    </tr>
    </tfoot>

</table>
