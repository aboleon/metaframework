@if (!$orderAccessor->serviceCart())
    <x-mfw::alert
        message="Pour effectuer des attributions il faut d'abord ajouter une prestation à la commande"/>
@else
    <div class="row gx-5 mb-4">
        <div class="col-xl-8">
            <h4>Commande</h4>

            <table class="table mt-3">
                <thead>
                <tr>
                    <th>Prestation</th>
                    <th>Famille</th>
                    <th>Date</th>
                    <th>Quantité</th>
                    <th class="text-end">Achété</th>
                    <th class="text-end">Distribué</th>
                    <th class="text-end">Restant</th>
                    <th></th>
                </tr>
                </thead>
                <tbody id="service-cart" data-shoppable="{{ \App\Models\EventManager\Sellable::class }}"
                       data-cart-id="{{ $orderAccessor->serviceCart()->first()->cart_id }}">
                @if ($orderAccessor->serviceCart())
                    @php
                        $summedQuantities = $orderAccessor->serviceAttributions()->groupBy('shoppable_id')
                        ->map(function ($items) {
                            return $items->sum('quantity');
                        })->toArray();
                    @endphp

                    @foreach($orderAccessor->serviceCart()->groupBy('service_id') as $grouped)
                        <x-order-service-attribution-row :grouped="$grouped"
                                                         :services="$event->sellableService->load('event.services')"
                                                         :event="$event"
                                                         :distributed="$summedQuantities"/>
                    @endforeach
                @endif
                </tbody>
            </table>
            <button class="btn btn-sm btn-success mb-4" type="button" id="service-distributor">Distribuer
                aux
                membres
                sélectionnés
            </button>
            <div id="service-cart-messages" data-ajax="{{ route('ajax') }}"></div>
        </div>

        <div class="col-xl-4">
            @include('orders.attributions.members')
        </div>
    </div>
    @php
        $event_service = $event->sellableService->pluck('title','id')->toArray();
        $orderAccessor->serviceAttributions()->load('service');
    @endphp

    <div class="mfw-line-separator mb-5 pb-2"></div>
    <h4 class="fs-3 fw-bold">Affectations aux membres</h4>
    <div id="service-attribution-messages" data-ajax="{{ route('ajax') }}"></div>
    <h4>Prestations</h4>
            <table class="table table-bordered">
                <thead>
                <th style="width: 40%;">Intitulé</th>
                <th class="text-center" style="width: 20%;">Date</th>
                <th class="text-center" style="width: 20%;">Quantité</th>
                <th class="text-center" style="width: 20%;">Affecté le</th>
                </thead>
            </table>
            @forelse($groupMembers as $member)
                <div class="member member-{{ $member->id }}">
                    <b class="d-block mb-1">{{ $member->name }}</b>
                    <small class="d-block error d-none text-danger mb-2 fw-bold"></small>
                    <table class="table">
                        <tbody>
                        @php
                            $memberServices = $orderAccessor->serviceAttributions()->filter(fn($a) => $a->event_contact_id == $member->id);
                        @endphp
                        @forelse($memberServices as $item)
                            <x-order-affected-service-row :attribution="$item" :services="$event_service"/>
                        @empty
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @empty
                <x-mfw::alert message="Aucun membre de groupe n'est associé à cet évènement"/>
            @endforelse

    <template id="affected-service">
        <x-order-affected-service-row :attribution="new \App\Models\Order\Cart\ServiceAttribution()"/>
    </template>

    @push('callbacks')
        <script>
            function postRemoveServiceAttribution(result) {
                if (!result.hasOwnProperty('error')) {
                    let row = $('tr.order-service-attribution-row' + result.input.identifier),
                        distributed = row.find('.distributed'),
                        remaining = row.find('.remaining');

                    distributed.text(produceNumberFromInput(distributed.text()) - produceNumberFromInput(result.to_restore));
                    remaining.text(produceNumberFromInput(remaining.text()) + produceNumberFromInput(result.to_restore));

                    $('tr.affected-service' + result.input.identifier).remove();
                }
            }

            function removeAttributionService() {

                $('.delete_attribution_service_row').click(function () {
                    let id = produceNumberFromInput($(this).attr('data-model-id')),
                        identifier = '.' + $(this).attr('data-identifier').replace(/\s+/g, '.');
                    $('#mfw-simple-modal').find('.btn-cancel').trigger('click');
                    id < 1
                        ? $(identifier).remove()
                        : ajax('action=removeServiceAttribution&callback=postRemoveServiceAttribution&id=' + id + '&identifier=' + identifier, $(identifier).closest('.member'));

                });
            }
        </script>
    @endpush
    @push('js')
        <script>
            const myServiceCart = new AttributionCart('service');
            myServiceCart.init();
        </script>
    @endpush

@endif
