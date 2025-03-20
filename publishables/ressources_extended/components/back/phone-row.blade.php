@props([
    "phone" => null,
    "phoneLabel" => "Téléphone",
    "namespace" => null,
])

@php
    $countryCodeName = $namespace ? $namespace . '.country_code' : 'country_code';
    $phoneName = $namespace ? $namespace . '.phone' : 'phone';
@endphp
<div class="row">
    <div class="col-sm-4">

        <x-mfw::select :label="__('mfw.geo.country_code')"
                       :name="$countryCodeName"
                       :values="array_merge(['' => '--- Choisissez ---'], \App\Helpers\PhoneCountryHelper::selectable())"
                       :affected="old($countryCodeName, $phone?->getCountry())"
                       :nullable="false" />
    </div>
    <div class="col-sm-8">
        <x-mfw::input :name="$phoneName"
                      label="{{ $phoneLabel }}"
                      :value="old($phoneName, $phone?->formatNational())" />
    </div>
</div>