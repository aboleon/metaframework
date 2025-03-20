<div class="invoiced {{ $invoiced ? 'd-none' : '' }}">
    <div class="row g-2 gx-2" id="service-selector">
        @foreach($event->sellableService->load('professions','participations')->groupBy('service_group') as $serviceGroup => $collection)

            @if($eventServices->has($serviceGroup))

                <div class="service-grouped col-12 mb-3"
                     data-service-group-id="{{ $serviceGroup }}">
                    <h5 class="fs-6 py-2 text-start badge fw-bold bg-dark me-3">{{ $eventServices[$serviceGroup]['name'] ?? 'Sans famille' }}</h5>
                    <span class="fw-bold text-dark">
                        Nb Max de résas par famille: {{ $eventServices[$serviceGroup]['unlimited'] ? 'sans limite' : $eventServices[$serviceGroup]['max'] }}
                </span>
                    <div class="row">
                        @foreach($collection->reject(fn($item) => !is_null($item->is_invitation))->sortBy(fn($item) => $item->service_date ? -\Carbon\Carbon::createFromFormat('d/m/Y', $item->service_date)->timestamp : PHP_INT_MIN)->values() as $service)
                            <x-order-service-selector-item :service="$service"
                                                           :event="$event"
                                                           :families="$eventServices"
                                                           :availability="$eventAccessor->availableSellablesStocks()"
                                                           :pecbooked="$event_contact['pec_bookings'] ?? []"/>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @if ($event->sellableService)
        <div class="text-center my-3">
            <button type="button" id="add-service-to-order" class="btn btn-secondary btn-sm">Ajouter
                à la commande
            </button>
        </div>
    @endif
</div>
