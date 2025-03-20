@php use MetaFramework\Interfaces\GooglePlacesInterface; @endphp
@props([
    'geo' => null,
    'field' => "wa_geo",
    'error' => [],
    'label' => null,
    'required' => [],
    'tag_required' => "required",
    'placeholder' => "",
    'hidden' => [
        'administrative_area_level_1_short',
        'administrative_area_level_1',
        'administrative_area_level_2',
        'country_code'
    ],
])

@php
    $readonly = [
        'street_number',
        'route',
        'locality',
        'postal_code',
    ];
    $random_id = Str::random(4);
    $labelRequired = fn($name) => in_array($name, $required) ? ' *' : '';
    $tagRequired = fn($name) => in_array($name, $required) ? $tag_required : '';
    $inputable = fn($name) => 'col-'.$name . ' ' . (in_array($name, $hidden) ? ' d-none' : '');
    $defaultTextAddress = $geo?->text_address ?? ($geo?->locality ?? null);
    $readonlies = fn($name) => !$defaultTextAddress && in_array($name, $readonly) ? ' lockable' : '';
@endphp


<div class="clearfix gmapsbar{{ $field }}" id="mapsbar_{{ $random_id }}">
    <div class="locationField mb-4" data-error="">
        @if ($label)
            <label for="geo_text_address_{{ $random_id }}"
                   class="form-label">{{ $label . $labelRequired('text_address') }}</label>
        @endif
        <input type="text"
               name="{{ $field }}[text_address]"
               value="{{ $error ? old($field.'.text_address') : $defaultTextAddress }}"
               class="g_autocomplete form-control {{ $tagRequired('text_address') }}"
               id="geo_text_address_{{ $random_id }}"
               placeholder="{{ $placeholder ?:  trans('mfw.geo.type_address') }}" {{ $tagRequired('text_address') }}>
            <div class="form-text">Remplissez cette adresse pour remplir les autres champs</div>
        <x-mfw::validation-error field="{{ $field }}[text_address]" />

        <div class="dynamic-error alert alert-danger" style="display: none;">Adresse non reconnue
        </div>
    </div>

    <div class="mb-3 row {{ $field }}_fields">
        <div class="mb-3 col-sm-4 {{ $inputable('street_number') }}">
            <x-mfw::input class="field street_number{{ $tagRequired('street_number') . $readonlies('street_number') }}"
                          :label="trans('mfw.geo.street_number')"
                          name="{{ $field }}[street_number]"
                          value="{{ $error ? old($field.'.street_number') : ($geo?->street_number ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.street_number')]"
                          :required="$tagRequired('street_number')"
                          :readonly="$readonlies('street_number')"
            />

        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('route') }}">
            <x-mfw::input class="field route{{ $tagRequired('route') . $readonlies('route') }}"
                          :label="trans('mfw.geo.route') . $labelRequired('route')"
                          name="{{ $field }}[route]"
                          value="{{ $error ? old($field.'.route') : ($geo?->route ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.route')]"
                          :readonly="$readonlies('route')"
            />
        </div>
        <div class="mb-3 col-sm-4 {{ $inputable('postal_code') }}">
            <x-mfw::input class="field postal_code{{ $tagRequired('postal_code') . $readonlies('postal_code') }}"
                          :label="trans('mfw.geo.postal_code') . $labelRequired('postal_code')"
                          name="{{ $field }}[postal_code]"
                          value="{{ $error ? old($field.'.postal_code') : ($geo?->postal_code ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.postal_code')]"
                          :readonly="$readonlies('postal_code')"
            />
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('locality') }}">
            <x-mfw::input class="field locality{{ $tagRequired('locality') . $readonlies('locality') }}"
                          :label="trans('mfw.geo.locality') . $labelRequired('locality')"
                          name="{{ $field }}[locality]"
                          value="{{ $error ? old($field.'.locality') : ($geo?->locality ?? '') }}"
                          :params="['placeholder'=> trans('mfw.geo.locality')]"
                          :readonly="$readonlies('locality')" />
        </div>
        <div class="mb-3 col-sm-4 {{ $inputable('administrative_area_level_2') }}">
            <x-mfw::input class="field administrative_area_level_2 {{ $tagRequired('administrative_area_level_2') }}"
                          :label="trans('mfw.geo.district') . $labelRequired('administrative_area_level_2')"
                          name="{{ $field }}[administrative_area_level_2]"
                          value="{{ $error ? old($field.'.administrative_area_level_2') : ($geo?->administrative_area_level_2 ?? '') }}" />
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('administrative_area_level_1') }}">
            <x-mfw::input class="field administrative_area_level_1 {{ $tagRequired('administrative_area_level_1') }}"
                          :label="trans('mfw.geo.region') . $labelRequired('administrative_area_level_1')"
                          name="{{ $field }}[administrative_area_level_1]"
                          value="{{ $error ? old($field.'.administrative_area_level_1') : ($geo?->administrative_area_level_1 ?? '') }}" />
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('administrative_area_level_1_short') }}">
            <x-mfw::input
                    class="field administrative_area_level_1_short {{ $tagRequired('administrative_area_level_1_short') }}"
                    :label="trans('mfw.geo.region') . $labelRequired('administrative_area_level_1_short')"
                    name="{{ $field }}[administrative_area_level_1_short]"
                    value="{{ $error ? old($field.'.administrative_area_level_1_short') : ($geo?->administrative_area_level_1_short ?? '') }}" />
        </div>
        <div class="mb-3 col-sm-4 {{ $inputable('country_code') }}">
            <x-mfw::input
                    class="field country_code {{ $tagRequired('country_code') }}"
                    :label="trans('mfw.geo.country_code') . $labelRequired('country_code')"
                    name="{{ $field }}[country_code]"
                    value="{{ $error ? old($field.'.country_code') : ($geo?->country_code ?? '') }}" />
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('country') }}">
            <x-mfw::input
                    class="field country {{ $tagRequired('country') }}"
                    :label="trans('mfw.geo.country') . $labelRequired('country')"
                    name="{{ $field }}[country]"
                    value="{{ $error ? old($field.'.country') : ($geo?->country_code ? \MetaFramework\Accessors\Countries::getCountryNameByCode($geo?->country_code) : '') }}"
                    readonly />
        </div>
    </div>
    <input type="hidden" class="wa_geo_lat lat" name="{{ $field }}[lat]"
           value="{{ $error ? old($field.'.lat') : ($geo?->lat ?? '') }}" />
    <input type="hidden" class="wa_geo_lon lon" name="{{ $field }}[lon]"
           value="{{ $error ? old($field.'.lon') : ($geo?->lon ?? '') }}" />
</div>



@push("js")
    <script>
        window.gmapCallbackAddress = function() {

            GoogleMapHelper.init('#geo_text_address_{{ $random_id }}', {
                change: function(address, place) {
                    const jWidget = $('#mapsbar_{{ $random_id }}');
                    const jDynamicError = jWidget.find('.dynamic-error');
                    jDynamicError.hide();

                    let placeType = GoogleMapHelper.getPlaceType(place);
                    if ('country' === placeType) {
                        jWidget.find('input.country').val(address.text_address);
                        jWidget.find('input.country_code').val(address.country_code);
                    } else if ('street_address' === placeType) {
                        jWidget.find('input.street_number').val(address.street_number);
                        jWidget.find('input.route').val(address.route);
                        jWidget.find('input.locality').val(address.locality);
                        jWidget.find('input.postal_code').val(address.postal_code);
                        jWidget.find('input.administrative_area_level_2').val(address.administrative_area_level_2);
                        jWidget.find('input.administrative_area_level_1').val(address.administrative_area_level_1);
                        jWidget.find('input.administrative_area_level_1_short').val(address.administrative_area_level_1_short);
                        jWidget.find('input.country').val(GoogleMapHelper.getCountryFromStreetAddressPlace(place));
                        jWidget.find('input.country_code').val(address.country_code);
                        jWidget.find('input.lat').val(address.latitude);
                        jWidget.find('input.lon').val(address.longitude);

                    } else {

                        jDynamicError.html('Ceci n\'est pas une adresse valide (type=' + placeType + ')');
                        jDynamicError.show();
                        console.log('Don\'t know what to do with this place type: ' + placeType);
                    }
                },
            });
        };
    </script>
@endpush
<x-google-map-helper callback="gmapCallbackAddress" />