@php
    $class = 'room-'.$room['id'];
    $vat_id = $accommodation->vat_id ?: $defaultVatId;
    $roomLabel = $availability->getRoomGroups()[$roomgroup]['rooms'][$room['id']] .' x ' . $room['capacity'];
    $room_price = floatval($room['price']);
    $pec_price = $room['pec'] ? $room_price - $room['pec_allocation'] : 0;
    $pec_allocation = $room['pec'] ? $room['pec_allocation'] : 0;
    $has_pec = $pec_eligible && !is_null($room['pec']) && $room['pec_allocation'];

@endphp
<td class="{{ $class }} type room-type"
    data-room-id="{{ $room['id'] }}"
    data-label="{{ $accommodation->hotel->name . ' '.$accommodation->hotel->stars.'*' .' '. $roomLabel .' / '. $roomgroupLabel}}"
    data-capacity="{{ $room['capacity'] }}"
    data-target="{{ $date.'-'.$room['id'] }}" data-date="{{ $date }}"
    data-readable-date="{{ $readableDate }}">
    <x-mfw::checkbox name="shopping_cart_accommodation.rooms."
                     class="d-flex"
                     :value="$room['id']"
                     :label="$roomLabel"
                     :params="!($availability->getAvailability()[$date][$roomgroup] ?? 0) ? ['disabled' => 'disabled'] : []"/>
</td>
<td class="{{ $class }} sell text-center"
    style="max-width: 120px"
    data-net="{{  \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($room_price, $vat_id) }}"
    data-vat="{{ \MetaFramework\Accessors\VatAccessor::vatForPrice($room_price, $vat_id) }}"
    data-pec-net="{{ $pec_price ? \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($pec_price, $vat_id) : 0 }}"
    data-pec-allocation-net="{{ $pec_price ? \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($pec_allocation, $vat_id) : 0 }}"
    data-pec-vat="{{ $pec_price ? \MetaFramework\Accessors\VatAccessor::vatForPrice($pec_price, $vat_id) : 0 }}"
    data-pec-allocation-vat="{{ $pec_price ? \MetaFramework\Accessors\VatAccessor::vatForPrice($pec_allocation, $vat_id) : 0 }}"
>
    {!! \App\Helpers\TextHelper::wrapInTag($room_price, ($has_pec ? 'del':'')) !!}
</td>
<td class="{{ $class }} text-center" data-has-pec="{{ $has_pec }}">
    {{ $has_pec ? $pec_price : ''  }}
</td>
<td class="{{ $class }} pec text-center" data-has-pec="{{ $has_pec }}">
    <i class="bi bi-check-circle-fill {{ $room['pec'] ? 'text-success':'text-secondary opacity-50' }}"></i>
</td>
<td class="{{ $class }} pec-allocation text-center" style="max-width: 120px"
    data-pec-allocation="{{ $room['pec_allocation'] }}">
    {{ $room['pec_allocation'] }}
</td>

<td class="{{ $class }} service" style="width: 15%">
    {{ $services[$room['service_id']] ?? 'Aucune' }}
</td>
<td class="{{ $class }} published">
    <i class="bi bi-check-circle-fill {{ $room['published'] ? 'text-success':'text-secondary opacity-50' }}"></i>
</td>
