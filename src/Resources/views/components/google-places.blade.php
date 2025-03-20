{{-- Pour activer la recherche Google Maps Places - class=gmpasbar --}}
@php
    $error = $errors->any();
@endphp
<div class="clearfix gmapsbar {{ $field }}" id="mapsbar_{{ $random_id }}">
    <div class="locationField" data-error="">

        @if ($label)
            <label for="geo_text_address_{{ $random_id }}"
                   class="form-label">{{ $label . $labelRequired('text_address') }}</label>
        @endif
        <input type="text"
               name="{{ $field }}[text_address]"
               value="{{ $error ? old($field.'.text_address') : $defaultTextAdress }}"
               class="g_autocomplete form-control {{ $tagRequired('text_address') }}"
               id="geo_text_address_{{ $random_id }}"
               placeholder="{{ $placeholder ?:  trans('mfw.geo.type_address') }}" {{ $tagRequired('text_address') }}>

        @if ($notice)
            <small class="pt-1 d-block text-secondary">{{ $notice }}</small>
        @endif
        <x-mfw::validation-error field="{{ $field }}[text_address]"/>
    </div>

    <div class="mb-3 row {{ $field }}_fields">
        <div class="mb-3 col-sm-4 {{ $inputable('street_number') }}">
            <x-mfw::input class="field street_number{{ $tagRequired('street_number') . $readonlies('street_number') }}"
                          :label="trans('mfw.geo.street_number')"
                          name="{{ $field }}[street_number]"
                          value="{{ $error ? old($field.'.street_number') : ($geo->street_number ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.street_number')]"
                          :required="$tagRequired('street_number')"
                          :readonly="$readonlies('street_number')"
            />

        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('route') }}">
            <x-mfw::input class="field route{{ $tagRequired('route') . $readonlies('route') }}"
                          :label="trans('mfw.geo.route') . $labelRequired('route')"
                          name="{{ $field }}[route]"
                          value="{{ $error ? old($field.'.route') : ($geo->route ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.route')]"
                          :readonly="$readonlies('route')"
            />
        </div>
        <div class="mb-3 col-sm-4 {{ $inputable('postal_code') }}">
            <x-mfw::input class="field postal_code{{ $tagRequired('postal_code') . $readonlies('postal_code') }}"
                          :label="trans('mfw.geo.postal_code') . $labelRequired('postal_code')"
                          name="{{ $field }}[postal_code]"
                          value="{{ $error ? old($field.'.postal_code') : ($geo->postal_code ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.postal_code')]"
                          :readonly="$readonlies('postal_code')"
            />
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('locality') }}">
            <x-mfw::input class="field locality{{ $tagRequired('locality') . $readonlies('locality') }}"
                          :label="trans('mfw.geo.locality') . $labelRequired('locality')"
                          name="{{ $field }}[locality]"
                          value="{{ $error ? old($field.'.locality') : ($geo->locality ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.locality')]"
                          :readonly="$readonlies('locality')"/>
        </div>
        <div class="mb-3 col-sm-4 {{ $inputable('administrative_area_level_2') }}">
            <x-mfw::input class="field administrative_area_level_2 {{ $tagRequired('administrative_area_level_2') }}"
                          :label="trans('mfw.geo.district') . $labelRequired('administrative_area_level_2')"
                          name="{{ $field }}[administrative_area_level_2]"
                          value="{{ $error ? old($field.'.administrative_area_level_2') : ($geo->administrative_area_level_2 ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('administrative_area_level_1') }}">
            <x-mfw::input class="field administrative_area_level_1 {{ $tagRequired('administrative_area_level_1') }}"
                          :label="trans('mfw.geo.region') . $labelRequired('administrative_area_level_1')"
                          name="{{ $field }}[administrative_area_level_1]"
                          value="{{ $error ? old($field.'.administrative_area_level_1') : ($geo->administrative_area_level_1 ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('administrative_area_level_1_short') }}">
            <x-mfw::input
                    class="field administrative_area_level_1_short {{ $tagRequired('administrative_area_level_1_short') }}"
                    :label="trans('mfw.geo.region') . $labelRequired('administrative_area_level_1_short')"
                    name="{{ $field }}[administrative_area_level_1_short]"
                    value="{{ $error ? old($field.'.administrative_area_level_1_short') : ($geo->administrative_area_level_1_short ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-2 {{ $inputable('country_code') }}">
            <x-mfw::input
                    class="field country_code {{ $tagRequired('country_code') }}"
                    :label="trans('mfw.geo.country_code') . $labelRequired('country_code')"
                    name="{{ $field }}[country_code]"
                    value="{{ $error ? old($field.'.country_code') : ($geo->country_code ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-5 {{ $inputable('country') }}">
            <x-mfw::input
                    class="field country {{ $tagRequired('country') }}"
                    :label="trans('mfw.geo.country') . $labelRequired('country')"
                    name="{{ $field }}[country]"
                    value="{{ $error ? old($field.'.country') : ($geo->country_code ? \MetaFramework\Accessors\Countries::getCountryNameByCode($geo->country_code) : '') }}"
                    readonly/>
        </div>
        {{-- TODO: continent
        <div class="mb-3 col-sm-5 {{ $inputable('continent') }}">
            <x-mfw::input
                class="field continent {{ $tagRequired('continent') }}"
                :label="trans('mfw.geo.continent') . $labelRequired('continent')"
                name="{{ $field }}[continent]"
                value="{{ $error ? old($field.'.continent') : ($geo->country_code ? \MetaFramework\Accessors\Countries::getCountryNameByCode($geo->country_code) : '') }}"
                readonly/>
        </div>
        --}}
    </div>
    <input type="hidden" class="wa_geo_lat" name="{{ $field }}[lat]"
           value="{{ $error ? old($field.'.lat') : ($geo->lat ?? '') }}"/>
    <input type="hidden" class="wa_geo_lon" name="{{ $field }}[lon]"
           value="{{ $error ? old($field.'.lon') : ($geo->lon ?? '') }}"/>
    <input type="hidden" class="address_type" name="{{ $field }}[address_type]"/>
    <input type="hidden" class="continent" name="{{ $field }}[continent]"/>
</div>
@if ($params)
    <span id="params_mapsbar_{{ $random_id }}" class="d-none">{!! collect($params)->toJson() !!}</span>
@endif
@once
    @push('js')
        <script src="{{ asset('vendor/mfw/components/google-places-geolocate.js') }}"></script>
        <script
                src="https://maps.googleapis.com/maps/api/js?key={{ config('mfw-api.google.places') }}&libraries=places&callback=initialize"></script>
    @endpush
@endonce
