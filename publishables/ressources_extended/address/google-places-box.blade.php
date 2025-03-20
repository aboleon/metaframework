@php $is_required = false; @endphp
<div class="clearfix gmapsbar">{{-- Pour activer la recherche Google Maps Places - class=gmpasbar --}}
    <div class="locationField">
        <input type="text" name="wa_geo[text_address]" value="{{ old('wa_geo.text_address') ?? ($geo?->text_address ?? '') }}" class="g_autocomplete form-control" placeholder="{{  trans('mfw.geo.type_address') }} *" {{ $is_required ? 'required' : null }}>
    </div>
    <div class="mb-3 row">
        <div class="col-sm-4">
            <label for="geo_postal_code" class="form-label">{{ trans('mfw.geo.postal_code') }}</label>
            <input type="text" name="wa_geo[postal_code]" value="{{ old('wa_geo.postal_code') ?? ($geo?->postal_code ?? '') }}" class="form-control postal_code" placeholder="{{ trans('mfw.geo.postal_code') }} *" id="geo_postal_code" {{ $is_required ? 'required' : null }} />
        </div>
        <div class="col-sm-8">
            <label for="geo_locality" class="form-label">{{ trans('mfw.geo.locality') }}</label>
            <input type="text" name="wa_geo[locality]" value="{{ old('wa_geo.locality') ?? ($geo?->locality ?? '') }}" class="form-control locality" placeholder="{{ trans('mfw.geo.locality') }} *" id="geo_locality" {{ $is_required ? 'required' : null }} />
        </div>
    </div>
    <div class="mb-3 row">
        <div class="col-sm-4">
            <label for="geo_street_number" class="form-label">{{ trans('mfw.geo.street_number') }}</label>
            <input class="field street_number form-control" name="wa_geo[street_number]" style="width: 99%" value="{{ old('wa_geo.street_number') ?? ($geo?->street_number ?? '') }}" placeholder="{{ trans('mfw.geo.street_number') }}" id="geo_street_number"/>
        </div>
        <div class="col-sm-8">
            <label for="geo_route" class="form-label">{{ trans('mfw.geo.route') }}</label>
            <input class="field route form-control" name="wa_geo[route]" value="{{ old('wa_geo.route') ?? ($geo?->route ?? '') }}" placeholder="{{ trans('mfw.geo.route') }} *"  id="geo_route" {{ $is_required ? 'required' : null }} />
        </div>
    </div>
    <div class="mb-3 row">
        <div class="col-sm-4">
            <label for="geo_country_code" class="form-label">{{ trans('mfw.geo.country_code') }}</label>
            <input class="field country_code form-control" name="wa_geo[country_code]" value="{{ old('wa_geo.country_code') ?? ($geo?->country_code ?? '') }}" placeholder="" id="geo_country_code"/>
        </div>
        <div class="col-sm-8">
            <label for="geo_country" class="form-label">{{ trans('mfw.geo.country') }}</label>
            <input class="field country form-control" name="wa_geo[country]" value="{{ old('wa_geo.country') ?? (isset($geo?->country_code) ? ($countries[$geo->country_code] ?? '') : '') }}" placeholder="{{ trans('mfw.geo.country') }} *" id="geo_country" {{ $is_required ? 'required' : null }} />
        </div>
    </div>
    <input type="hidden" class="wa_geo_lat" name="wa_geo[lat]" value="{{ old('wa_geo.lat') ?? ($geo?->lat ?? '') }}"/>
    <input type="hidden" class="wa_geo_lon" name="wa_geo[lon]" value="{{ old('wa_geo.lon') ?? ($geo?->lon ?? '') }}"/>
</div>
@push('js')
    <script src="{{ asset('js/google-places-geolocate.js') }}"></script>
    <script src="//maps.googleapis.com/maps/api/js?key={!! config('app.google_places_api_key') !!}&libraries=places&callback=initialize"></script>
@endpush
