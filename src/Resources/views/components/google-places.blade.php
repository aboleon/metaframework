{{-- Pour activer la recherche Google Maps Places - class=gmpasbar --}}
@php
    $error = $errors->any();
    $resolver = config('mfw-inputable.countries_resolver');
    $countryName = '';
    if ($error) {
        $countryName = old($field . '.country');
    } elseif (!empty($geo->country)) {
        $countryName = $geo->country;
    } elseif (!empty($geo->country_code) && $resolver && class_exists($resolver) && method_exists($resolver, 'getCountryNameByCode')) {
        $countryName = $resolver::getCountryNameByCode($geo->country_code);
    }
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
               placeholder="{{ $placeholder ?: trans('mfw-inputable-geo.type_address') }}" {{ $tagRequired('text_address') }}>

        @if ($notice)
            <small class="pt-1 d-block text-secondary">{{ $notice }}</small>
        @endif
        <x-mfw-inputable::validation-error field="{{ $field }}[text_address]"/>
    </div>

    <div class="my-3 row {{ $field }}_fields">
        <div class="mb-3 col-sm-4 {{ $inputable('street_number') }}">
            <x-mfw-inputable::input class="field street_number{{ $tagRequired('street_number') . $readonlies('street_number') }}"
                                :label="trans('mfw-inputable-geo.street_number')"
                                name="{{ $field }}[street_number]"
                                value="{{ $error ? old($field.'.street_number') : ($geo->street_number ?? '') }}"
                                :params="['placeholder'=> trans('mfw-inputable-geo.street_number')]"
                                :required="$tagRequired('street_number')"
                                :readonly="$readonlies('street_number')"
            />

        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('route') }}">
            <x-mfw-inputable::input class="field route{{ $tagRequired('route') . $readonlies('route') }}"
                                :label="trans('mfw-inputable-geo.route') . $labelRequired('route')"
                                name="{{ $field }}[route]"
                                value="{{ $error ? old($field.'.route') : ($geo->route ?? '') }}"
                                :params="['placeholder'=> trans('mfw-inputable-geo.route')]"
                                :readonly="$readonlies('route')"
            />
        </div>
        <div class="mb-3 col-sm-4 {{ $inputable('postal_code') }}">
            <x-mfw-inputable::input class="field postal_code{{ $tagRequired('postal_code') . $readonlies('postal_code') }}"
                                :label="trans('mfw-inputable-geo.postal_code') . $labelRequired('postal_code')"
                                name="{{ $field }}[postal_code]"
                                value="{{ $error ? old($field.'.postal_code') : ($geo->postal_code ?? '') }}"
                                :params="['placeholder'=> trans('mfw-inputable-geo.postal_code')]"
                                :readonly="$readonlies('postal_code')"
            />
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('locality') }}">
            <x-mfw-inputable::input class="field locality{{ $tagRequired('locality') . $readonlies('locality') }}"
                                :label="trans('mfw-inputable-geo.locality') . $labelRequired('locality')"
                                name="{{ $field }}[locality]"
                                value="{{ $error ? old($field.'.locality') : ($geo->locality ?? '') }}"
                                :params="['placeholder'=> trans('mfw-inputable-geo.locality')]"
                                :readonly="$readonlies('locality')"/>
        </div>
        <div class="mb-3 col-sm-4 {{ $inputable('administrative_area_level_2') }}">
            <x-mfw-inputable::input class="field administrative_area_level_2 {{ $tagRequired('administrative_area_level_2') }}"
                                :label="trans('mfw-inputable-geo.district') . $labelRequired('administrative_area_level_2')"
                                name="{{ $field }}[administrative_area_level_2]"
                                value="{{ $error ? old($field.'.administrative_area_level_2') : ($geo->administrative_area_level_2 ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('administrative_area_level_1') }}">
            <x-mfw-inputable::input class="field administrative_area_level_1 {{ $tagRequired('administrative_area_level_1') }}"
                                :label="trans('mfw-inputable-geo.region') . $labelRequired('administrative_area_level_1')"
                                name="{{ $field }}[administrative_area_level_1]"
                                value="{{ $error ? old($field.'.administrative_area_level_1') : ($geo->administrative_area_level_1 ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-8 {{ $inputable('administrative_area_level_1_short') }}">
            <x-mfw-inputable::input
                    class="field administrative_area_level_1_short {{ $tagRequired('administrative_area_level_1_short') }}"
                    :label="trans('mfw-inputable-geo.region') . $labelRequired('administrative_area_level_1_short')"
                    name="{{ $field }}[administrative_area_level_1_short]"
                    value="{{ $error ? old($field.'.administrative_area_level_1_short') : ($geo->administrative_area_level_1_short ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-2 {{ $inputable('country_code') }}">
            <x-mfw-inputable::input
                    class="field country_code {{ $tagRequired('country_code') }}"
                    :label="trans('mfw-inputable-geo.country_code') . $labelRequired('country_code')"
                    name="{{ $field }}[country_code]"
                    value="{{ $error ? old($field.'.country_code') : ($geo->country_code ?? '') }}"/>
        </div>
        <div class="mb-3 col-sm-5 {{ $inputable('country') }}">
            <x-mfw-inputable::input
                    class="field country {{ $tagRequired('country') }}"
                    :label="trans('mfw-inputable-geo.country') . $labelRequired('country')"
                    name="{{ $field }}[country]"
                    value="{{ $countryName }}"
                    readonly/>
        </div>
    </div>
    <input type="hidden" class="mfw_geo_lat" name="{{ $field }}[lat]"
           value="{{ $error ? old($field.'.lat') : ($geo->lat ?? '') }}"/>
    <input type="hidden" class="mfw_geo_lon" name="{{ $field }}[lon]"
           value="{{ $error ? old($field.'.lon') : ($geo->lon ?? '') }}"/>
    <input type="hidden" class="place_id" name="{{ $field }}[place_id]"
           value="{{ $error ? old($field.'.place_id') : ($geo->place_id ?? '') }}"/>
    <input type="hidden" class="address_type" name="{{ $field }}[address_type]"/>
    <input type="hidden" class="continent" name="{{ $field }}[continent]"/>
</div>
@if ($params)
    <span id="params_mapsbar_{{ $random_id }}" class="d-none">{!! collect($params)->toJson() !!}</span>
@endif
@once
    @push('css')
        <style>
            /* Style Google Places PlaceAutocompleteElement to match Bootstrap form-control */
            gmp-place-autocomplete {
                display: block;
                width: 100%;
                --gmp-input-border: none;
            }
        </style>
    @endpush
    @push('js')
        @php($placesKey = config('mfw-inputable.google.places_api_key'))
        @if($placesKey)
            <script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]);for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
                key: "{{ $placesKey }}",
                v: "weekly"
            });</script>
        @endif
        <script src="{{ asset('vendor/mfw-inputable/components/google-places-geolocate.js') }}"></script>
    @endpush
@endonce
