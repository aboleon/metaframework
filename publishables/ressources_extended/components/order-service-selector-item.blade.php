<div class="col-sm-6 service-item text-dark selector-service-{{ $service->id }}"
     data-id="{{ $service->id }}"
     data-pec-enabled="{{ (int)$service->pec_eligible }}"
     data-pec-max="{{ (int)$service->pec_max_pax }}"
     data-stock="{{ $availability[$service->id] }}"
     data-unlimited="{{ (int)$service->stock_unlimited }}"
     data-pec-booked="{{ $pecbooked[$service->id] ?? 0 }}">
    <div class="d-flex justify-content-between">
        @php
            $params = [
                'data-max'=> (int)($group->max) ?: 1,
                'data-restriction-type'=> 'group',
                'data-date' => $service->getRawOriginal('service_date')
            ];

            if (!$service->stock_unlimited && $availability[$service->id] < 1) {
                $params['disabled'] = 'disabled';
            }
        @endphp
        <div class="d-flex">
            <x-mfw::checkbox :params="$params"
                             class="main" name="service_checkable." :value="$service->id"
                             :label="$service->title .' '. $service->accessorDate()"/>
            @if ($service->pec_eligible)
                <span class="ms-2 fw-bold">
                    <i class="bi bi-check-circle-fill text-success"></i> PEC</span>
                <span
                    class="max_pec">{!! (int)$service->pec_max_pax ? '&nbsp;(max '.$service->pec_max_pax.')' :'' !!}</span>

            @endif
        </div>
        <a href="{{ route('panel.manager.event.sellable.edit', ['event'=>$event->id, 'sellable' => $service->id]) }}"
           target="_blank" class="fs-6 float-end mfw-edit-link btn btn-sm btn-secondary smaller mb-1"
           data-bs-toggle="tooltip"
           data-bs-placement="top" data-bs-title="Éditer">
            <i class="fas fa-pen"></i></a>
    </div>
    <span class="bluie mb-1">
        Stock restant :</span> {!! !$service->stock_unlimited ? '<span class="stock-remaining">'.$availability[$service->id] .'</span>' : 'illimité' !!}
    <span class="ms-3 bluie">Prix :</span>
    @forelse($service->prices as $price)
        <span class="mb-1 price{{ $service->pec_eligible ? ' pec_eligible' : '' }}"
              data-price-id="{{ $price->id }}"
              data-vat_id="{{ $service->vat_id }}"
              data-net="{{ \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($price->price, $service->vat_id) }}"
              data-vat="{{ \MetaFramework\Accessors\VatAccessor::vatForPrice($price->price, $service->vat_id) }}"
              data-price="{{ $price->price }}"
              data-ends="{{ $price->getRawOriginal('ends') }}">
                         <span>{{ $price->price }}</span> € {{ $price->ends ? "jusqu'au ".$price->ends: 'sans date' }}
            @if(!$loop->last)
                |
            @endif
                </span>
    @empty
        Aucun prix configuré
    @endforelse

    <small class="d-block">
        <b>Participations:</b> {{ $service->participations->pluck('name')->join(', ') ?: 'Sans restriction' }}
        <b>Professions : </b>{{ $service->professions->pluck('name')->join(', ') ?: 'Sans restriction' }}
        <b>Prestation liée à une famille obligatoire
            : </b>{{ $families[$service->service_group_combined]['name'] ?? 'non' }}
    </small>

    <small class="d-block text-danger d-none error"></small>

</div>
