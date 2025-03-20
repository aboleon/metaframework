@php
    $error = $errors->any();
@endphp
<div class="col-xl-6 mb-3 {{ $accessor->isCompany() ? 'd-none': '' }}">
    <x-mfw::input name="profile[passport_first_name]" :label="__('forms.fields.passport_first_name')" :value="$error ? old('profile.passport_first_name') : $account?->profile?->passport_first_name"/>
</div>
<div class="col-xl-6 mb-3 {{ $accessor->isCompany() ? 'd-none': '' }}">
    <x-mfw::input name="profile[passport_last_name]" :label="__('forms.fields.passport_last_name')" :value="$error ? old('profile.passport_last_name') : $account?->profile?->passport_last_name"/>
</div>
<div class="col-lg-12 mb-3">
    <x-mfw::input-date-mask :label="__('forms.fields.birth')" name="profile[birth]" :value="$error ? old('profile.birth') : $account->profile?->birth?->format('d/m/Y')"/>
</div>
