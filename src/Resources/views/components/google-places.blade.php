{{-- Pour activer la recherche Google Maps Places - class=gmpasbar --}}
@php
    $error = $errors->any();
    $street_number_required = $params['required']['street_number'] ?? false;
    $route_required = $params['required']['route'] ?? false;
    $postal_code_required = $params['required']['postal_code'] ?? false;
    $locality_required = $params['required']['locality'] ?? false;
    $administrative_area_level_1_required = $params['required']['administrative_area_level_1'] ?? false;
    $administrative_area_level_1_short_required = $params['required']['administrative_area_level_1_short'] ?? false;
    $administrative_area_level_2_required = $params['required']['administrative_area_level_2'] ?? false;
    $country_code_required = $params['required']['country_code'] ?? false;
    $country_required = $params['required']['country'] ?? false;
@endphp
<div class="clearfix gmapsbar {{ $field }}" id="mapsbar_{{ $random_id }}">
    <div class="locationField" data-error="">

        @if ($label)
            <label for="geo_text_address_{{ $random_id }}" class="form-label">{{ $label }}</label>
        @endif
        <input type="text"
               name="{{ $field }}[text_address]"
               value="{{ $error ? old($field.'.text_address') : ($geo->text_address ?? ($geo->locality ?? '')) }}"
               class="g_autocomplete form-control"
               id="geo_text_address_{{ $random_id }}"
               placeholder="{{ $placeholder ?:  trans('mfw.geo.type_address') }}"
        >
        <x-mfw::validation-error field="{{ $field }}[text_address]" />
    </div>

    <div class="mb-3 row {{ $field }}_fields">
        <div class="mb-3 col-sm-4 col-street_number">
            <label for="geo_street_number_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.street_number') . ($street_number_required?' *':'') }}</label>
            <input class="field street_number form-control {{ $tagRequired('street_number') }}"
                   name="{{ $field }}[street_number]"
                   style="width: 99%"
                   value="{{ $error ? old($field.'.street_number') : ($geo->street_number ?? '') }}"
                   id="geo_street_number_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[street_number]" />
        </div>
        <div class="mb-3 col-sm-8 col-route">
            <label for="geo_route_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.route') . ($route_required?' *':'') }}</label>
            <input class="field route form-control {{ $tagRequired('route') }}"
                   name="{{ $field }}[route]"
                   value="{{ $error ? old($field.'.route') : ($geo->route ?? '') }}"
                   id="geo_route_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[route]" />
        </div>
        <div class="mb-3 col-sm-4 col-postal_code">
            <label for="geo_postal_code_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.postal_code') . ($postal_code_required?' *':'') }}</label>
            <input type="text"
                   name="{{ $field }}[postal_code]"
                   value="{{ $error ? old($field.'.postal_code') : ($geo->postal_code ?? '') }}"
                   class="form-control postal_code {{ $tagRequired('postal_code') }}"
                   id="geo_postal_code_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[postal_code]" />
        </div>
        <div class="mb-3 col-sm-8 col-locality">
            <label for="geo_locality_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.locality') . ($locality_required?' *':'') }}</label>
            <input type="text"
                   name="{{ $field }}[locality]"
                   value="{{ $error ? old($field.'.locality') : ($geo->locality ?? '') }}"
                   class="form-control locality {{ $tagRequired('locality') }}"
                   id="geo_locality_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[locality]" />
        </div>
        <div class="mb-3 col-sm-4 col-administrative_area_level_2">
            <label for="geo_district_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.district') . ($administrative_area_level_2_required?' *':'') }}</label>
            <input class="field administrative_area_level_2 form-control {{ $tagRequired('administrative_area_level_2') }}"
                   name="{{ $field }}[administrative_area_level_2]"
                   value="{{ $error ? old($field.'.administrative_area_level_2') : ($geo->administrative_area_level_2 ?? '') }}"
                   id="geo_district_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[administrative_area_level_2]" />
        </div>
        <div class="mb-3 col-sm-8 col-administrative_area_level_1">
            <label for="geo_region_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.region') . ($administrative_area_level_1_required?' *':'') }}</label>
            <input class="field administrative_area_level_1 form-control {{ $tagRequired('administrative_area_level_1') }}"
                   name="{{ $field }}[administrative_area_level_1]"
                   value="{{ $error ? old($field.'.administrative_area_level_1') : ($geo->administrative_area_level_1 ?? '') }}"
                   id="geo_region_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[administrative_area_level_1]" />
        </div>
        <div class="mb-3 col-sm-8 d-none col-administrative_area_level_1_short">
            <label for="geo_region_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.region') . ($administrative_area_level_1_short_required?' *':'') }}</label>
            <input class="field administrative_area_level_1_short form-control"
                   name="{{ $field }}[administrative_area_level_1_short]"
                   value="{{ $error ? old($field.'.administrative_area_level_1_short') : ($geo->administrative_area_level_1_short ?? '') }}"
                   id="geo_region_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[administrative_area_level_1_short]" />
        </div>
        <div class="mb-3 col-sm-4 col-country_code">
            <label for="geo_country_code_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.country_code') . ($country_code_required?' *':'') }}</label>
            <input class="field country_code form-control"
                   name="{{ $field }}[country_code]"
                   value="{{ $error ? old($field.'.country_code') : ($geo->country_code ?? '') }}"
                   placeholder=""
                   id="geo_country_code_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[country_code]" />
        </div>
        <div class="mb-3 col-sm-8 col-country">
            <label for="geo_country_{{ $random_id }}"
                   class="form-label">{{ trans('mfw.geo.country') . ($country_required?' *':'') }}</label>
            <input class="field country form-control {{ $tagRequired('country') }}"
                   name="{{ $field }}[country]"
                   value="{{ $error ? old($field.'.country') : ($geo->country_code ? \MetaFramework\Accessors\Countries::getCountryNameByCode($geo->country_code) : '') }}"
                   id="geo_country_{{ $random_id }}" />
            <x-mfw::validation-error field="{{ $field }}[country]" />
        </div>
    </div>
    <input type="hidden"
           class="wa_geo_lat"
           name="{{ $field }}[lat]"
           value="{{ $error ? old($field.'.lat') : ($geo->lat ?? '') }}" />
    <input type="hidden"
           class="wa_geo_lon"
           name="{{ $field }}[lon]"
           value="{{ $error ? old($field.'.lon') : ($geo->lon ?? '') }}" />
</div>
@if ($params)
    <span id="params_mapsbar_{{ $random_id }}"
          class="d-none">{!! collect($params)->toJson() !!}</span>
@endif
@once
    @push('js')
        <script src="{{ asset('vendor/mfw/components/google-places-geolocate.js') }}"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('mfw-api.google.places') }}&libraries=places&callback=initialize"></script>
    @endpush
@endonce