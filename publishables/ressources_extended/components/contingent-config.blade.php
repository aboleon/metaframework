@php
$room_id = $config->room_id ?: 'nullable';
@endphp
<td class="room-{{ $room_id }} type">{{ $room ? \App\Accessors\Dictionnaries::entry('type_chambres',  $room->room_id)->name . ' x ' . $room->capacity : 'NC' }}
    <x-front.debugmark title="id={{$room_id}}" />
</td>
<td class="room-{{ $room_id }} sell" style="max-width: 120px">
    <div class="input-group">
        <x-mfw::number name="{{ $row }}[rooms][{{ $room_id }}][sell]" :value="$config->sell" step="0.1"/>
        <span class="input-group-text">€</span>
    </div>

</td>
<td class="room-{{ $room_id }} buy" style="max-width: 120px">
    <div class="input-group">
        <x-mfw::number name="{{ $row }}[rooms][{{ $room_id }}][buy]" :value="$config->buy" step="0.1"/>
        <span class="input-group-text">€</span>
    </div>
</td>
<td class="room-{{ $room_id }} pec">
    <x-mfw::checkbox :switch="true" name="{{ $row }}[rooms][{{ $room_id }}][pec]" value="1" :affected="collect($config->pec)"/>
</td>
<td class="room-{{ $room_id }} pec-allocation" style="max-width: 120px">
    <div class="input-group">
        <x-mfw::number name="{{ $row }}[rooms][{{ $room_id }}][pec-allocation]" :value="$config->pec_allocation" step="0.1"/>
        <span class="input-group-text">€</span>
    </div>
</td>
<td class="room-{{ $room_id }} service" style="width: 15%">
    <x-mfw::select :values="$services" :affected="$config->service_id" defaultselecttext="Aucune" name="{{ $row }}[rooms][{{ $room_id }}][service]"/>
</td>
<td class="room-{{ $room_id }} published">
    <x-mfw::checkbox :switch="true" name="{{ $row }}[rooms][{{ $room_id }}][published]" value="1" :affected="collect($config->published)"/>
</td>
@if ($deletable)
    <td class="rowspan deletable align-top" rowspan="{{ $rowspan }}" style="width: 50px">
        <x-mfw::simple-modal id="delete_contingent"
                             class="btn btn-danger btn-sm mt-2"
                             title="Suppression d'une ligne de contingent"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="ajaxDeleteContingentRow"
                             :identifier="$row"
                             :modelid="$config->contingent_id"
                             text="Supprimer"/>
    </td>

@endif
