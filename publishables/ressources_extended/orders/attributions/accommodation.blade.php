@if (!$orderAccessor->accommodationCart())
    <x-mfw::alert
        message="Pour effectuer des attributions il faut d'abord ajouter une prestation à la commande"/>

@else
    @php
        $hotels = \App\Accessors\EventManager\Accommodations::hotelLabelsWithStatus($event);

    $locale = app()->getLocale();
    $ordered = collect($orderAccessor->accommodationAttributions())->groupBy(['event_hotel_id', 'date']);
    $rooms = $ordered->flatten()->map(fn($item) => [
    'capacity' => $item->capacity,
    'room_group_id' => $item->room_group_id,
    'room_id' => $item->room_id,
    'room_category' => json_decode($item->room_category)->{$locale},
    'room'=> json_decode($item->room)->{$locale}
    ]);

    @endphp

    <div class="row gx-5 mb-4">
        <div class="col-xl-8" id="{{ \App\Enum\OrderCartType::ACCOMMODATION->value }}-cart">
            <h4>Hébergement</h4>
            @foreach($ordered as $item)
                @php
                    $hotel = $item->first()->first();
                @endphp
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center">
                            <h5 class="fs-6 fw-bold py-2 px-3 badge rounded-pill text-bg-warning">{{ $hotel->hotel_name }}</h5>
                            @if($hotel->stars)
                                <ul class="list-inline ms-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <li class="list-inline-item me-0 small">
                                            @if ($i <= $hotel->stars)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        </li>
                                    @endfor
                                </ul>
                        </div>

                        <div class="fs-12">
                            <i class="bi bi-geo-alt-fill"></i>
                            {{ $hotel->hotel_address }}
                        </div>
                        @endif
                    </div>

                </div>
                <table class="table">
                    <thead>
                    <tr>
                        <th class="text-dark">Date</th>
                        <th class="text-dark">Type</th>
                        <th class="text-end text-dark">Quantité</th>
                        <th class="text-end text-dark">Acheté</th>
                        <th class="text-end text-dark">Attributions</th>
                        <th class="text-end text-dark">Restants</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($item as $date => $entries)
                        <tr>
                            <td rowspan="{{ $entries->count()+1 }}">
                                {{ \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y') }}
                            </td>
                        </tr>
                        @foreach($entries as $item)
                            <tr class="order-{{ \App\Enum\OrderCartType::ACCOMMODATION->value }}-attribution-row {{ $item->room_id.'-'.$date }}"
                                data-date="{{ $date }}">
                                <td>
                                    <x-mfw::checkbox name="shopping_cart_accommodation[id][]"
                                                     value="{{ $item->room_id }}"
                                                     label="{{ json_decode($item->room)->{$locale} }} x {{ $item->capacity }}p.
                                    <br><small>{!! json_decode($item->room_category)->{$locale} !!}</small>"/>
                                </td>
                                <td class="qty">
                                    <x-mfw::number name="shopping_cart_accommodation[qty][]" class="qty" value="1"
                                                   min="1"
                                                   :params="[
                'data-room-id' => $item->room_id,
                'data-bought' => $item->total_quantity,
                'data-distributed' => ($item->attributed ?? 0),
                'data-remaining' => $item->total_quantity - ($item->attributed ?? 0)
            ]"/>
                                </td>
                                <td class="text-end bought">{{ $item->total_quantity }}</td>
                                <td class="text-end distributed">{{ ($item->attributed ?? 0) }}</td>
                                <td class="text-end remaining pe-3">{{ $item->total_quantity - ($item->attributed ?? 0) }}</td>
                            </tr>
                    @endforeach
                    @endforeach
                </table>
            @endforeach

            <button class="btn btn-sm btn-success mb-4" type="button" id="accommodation-distributor">Distribuer
                aux
                membres
                sélectionnés
            </button>
            <div id="accommodation-cart-messages" data-ajax="{{ route('ajax') }}"></div>
        </div>

        <div class="col-xl-4">
            @include('orders.attributions.members')
        </div>
    </div>

    <div class="mfw-line-separator mb-5 pb-2"></div>
    <h4 class="fs-3 fw-bold">Affectations aux membres</h4>
    <div id="accommodation-attribution-messages" data-ajax="{{ route('ajax') }}"></div>

    <table class="table table-bordered">
        <thead>
        <th style="width: 40%;" class="align-middle">Chambre</th>
        <th class="text-center" style="width: 20%;">Date</th>
        <th class="text-center" style="width: 20%;">Quantité</th>
        <th class="text-center" style="width: 20%;">Affectée le</th>

        </thead>
    </table>
    @forelse($groupMembers as $member)
        <div class="member member-{{ $member->id }}">
            <b class="d-block mb-1">{{ $member->name }}</b>
            <small class="d-block error d-none text-danger mb-2 fw-bold"></small>
            <table class="table table-bordered">
                <tbody>
                @php
                    $memberServices = $order->accommodationAttributions->filter(fn ($item) => $item->order_id = $order->id && $item->event_contact_id == $member->id);
                @endphp
                @foreach($memberServices as $attribution)
                    @php
                        $identifier = \App\Enum\OrderCartType::ACCOMMODATION->value. '-'.$attribution->id;
                    $room = $rooms->where('room_id', $attribution->shoppable_id)->first();
                    @endphp

                    <tr class="text-center affected-service member-{{ $member->id .' ' .\App\Enum\OrderCartType::ACCOMMODATION->value .'-'. $attribution->shoppable_id .'-'.$attribution->attributes['date'] }} {{ $identifier }}">
                        <td class="align-middle text-start" style="width: 40%;">
                            {!! $room['room'] .' x ' . $room['capacity'] .'p. / '. $room['room_category'] !!}
                        </td>
                        <td style="width: 20%"
                            class="service-date">{{ Carbon\Carbon::createFromFormat('Y-m-d',$attribution->attributes['date'])->format('d/m/Y') }}</td>
                        <td class="align-middle qty" style="width: 20%;"
                            data-qty="{{ $attribution->quantity }}"
                            data-room-id="{{ $attribution->shoppable_id }}"
                            data-date="{{ $attribution->attributes['date'] }}"
                            data-event-contact-id="{{ $attribution->event_contact_id }}">{{ $attribution->quantity ?: 0 }}</td>
                        <td class="align-middle affected-date" style="width: 20%">
                            <div class="d-flex justify-content-around align-items-center">
                                {{ $attribution->created_at?->format('d/m/Y') }}
                                <x-mfw::simple-modal id="delete_attribution_accommodation_row"
                                                     class="btn mfw-bg-red btn-sm ms-2"
                                                     title="Suppression d'une attribution de chambre"
                                                     confirmclass="btn-danger"
                                                     confirm="Supprimer cette attribution"
                                                     callback="removeAttributionAccommodation"
                                                     :identifier="$identifier"
                                                     :modelid="$attribution->id"
                                                     text='<i class="fas fa-trash"></i>'/>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <x-mfw::alert message="Aucun membre de groupe n'est associé à cet évènement"/>
    @endforelse

    <template id="affected-accommodation">
        <x-order-affected-accommodation-row :attribution="new \App\Models\Order\Cart\AccommodationAttribution()"
                                            :hotels="$hotels" :rooms="$rooms"/>
    </template>



    @push('callbacks')
        <script>
            function postRemoveAttributionAccommodation(result) {
                if (!result.hasOwnProperty('error')) {
                    let row = $('tr.order-accommodation-attribution-row.' + result.model.shoppable_id + '-' + result.model.attributes.date),
                        distributed = row.find('.distributed'),
                        remaining = row.find('.remaining');

                    distributed.text(produceNumberFromInput(distributed.text()) - produceNumberFromInput(result.to_restore));
                    remaining.text(produceNumberFromInput(remaining.text()) + produceNumberFromInput(result.to_restore));
                    $('tr.affected-service.' + result.input.identifier).remove();
                }
            }

            function postCreateAccommodationAttributions(result) {
                let members = $('#members').find(':checked');

                members.each(function () {
                    let memberId = $(this).val();

                    Object.keys(result.stored).forEach(key => {
                        result.stored[key]
                            .sort((a, b) => new Date(a.date) - new Date(b.date)) // Sort items by date
                            .forEach(item => {
                                if (item.member_id == memberId) {
                                    let memberRow = $(`.${item.identifier}`);

                                    let totalQty = item.qty;
                                    let firstItemDate = item.date;
                                    let oldQty = produceNumberFromInput(memberRow.find('.qty.' + key + '-' + firstItemDate).text());

                                    memberRow.find('.qty').text(oldQty + totalQty).attr('data-qty', oldQty + totalQty).attr('data-room-id', key).attr('data-date', item.date).attr('data-event-contact-id', item.member_id);

                                    memberRow.find('td.service-date').text(item.date_formated);
                                    memberRow.find('a').attr('data-model-id', item.id);

                                    let attributionRow = $(`.order-accommodation-attribution-row.${key}-${firstItemDate}`);
                                    let distributed = attributionRow.find('.distributed');
                                    let remaining = attributionRow.find('.remaining');

                                    distributed.text(produceNumberFromInput(distributed.text()) + totalQty);
                                    remaining.text(produceNumberFromInput(remaining.text()) - totalQty);

                                }
                            });
                    });
                });
            }

            function removeAttributionAccommodation() {

                $('.delete_attribution_accommodation_row').off().click(function () {
                    let id = produceNumberFromInput($(this).attr('data-model-id')),
                        identifier = $(this).attr('data-identifier');
                    $('#mfw-simple-modal').find('.btn-cancel').trigger('click');
                    ajax('action=removeAccommodationAttribution&callback=postRemoveAttributionAccommodation&id=' + id + '&identifier=' + identifier, $('#accommodation-attribution-messages'));

                });
            }
        </script>
    @endpush
    @push('js')
        <script>
            const myServiceCart = new AttributionCart('accommodation');
            myServiceCart.init();
        </script>
    @endpush

@endif
