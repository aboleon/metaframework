<div class="invoiced {{ $invoiced && !$is_amendable ? 'd-none' : '' }}">

    @if (!$hotels)
        <x-mfw::alert message="Aucun hébergement n'est activé pour cet évènement."/>
    @else
        <div class="row mb-4" id="order-accommodation-search">
            @php
                $mindate = Carbon\Carbon::createFromFormat('d/m/Y', $event->starts)->subDays(2)->format('d/m/Y');
                $maxdate = Carbon\Carbon::createFromFormat('d/m/Y', $event->ends)->addDays(2)->format('d/m/Y');
            @endphp
            <div class="col-md-3">
                <x-mfw::datepicker name="hotel_entry_date"
                                   label="Arrivée"
                                   config="allowInput=true,minDate={{ $mindate }},maxDate={{ $maxdate }}"
                                   :params="['placeholder'=>'Choisir ou taper une date...','disabled'=>'disabled']"/>
            </div>
            <div class="col-md-3">
                <x-mfw::datepicker name="hotel_out_date"
                                   label="Départ"
                                   config="allowInput=true,minDate={{ Carbon\Carbon::createFromFormat('d/m/Y', $event->starts)->subDays(1)->format('d/m/Y') }},maxDate={{ $maxdate }}"
                                   :params="['placeholder'=>'Choisir ou taper une date...','disabled'=>'disabled']"/>
            </div>

            <div class="col-md-6">
                <b class="d-block mb-2">Hébergement</b>
                <div id="accommodation-selector"
                     @class(['dropdown w-100', 'amendable' => $is_amendable]) data-event="{{ $event->id }}">
                    <button id="accommodation_dropdownMenuButton"
                            class="form-control text-decoration-none form-select dropdown-toggle w-100 text-start"
                            href="#"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                        Séléctionner un hébergement
                    </button>
                    <ul class="dropdown-menu w-100">
                        @foreach($hotels as $hotel_id => $hotel)
                            <li class="dropdown-item pt-{{ $loop->index > 0 ? 2:0 }}" data-id="{{ $hotel_id }}">
                                {!! $hotel !!}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

    @endif

    <div id="show-accommodation-recap" data-ajax="{{ route('ajax') }}">
        <div class="messages"></div>
        <div class="recap"></div>
    </div>

    <div class="text-center d-none" id="add-accommodation-room-to-order">
        <button type="button" class="btn btn-success">Ajouter</button>
    </div>
</div>
