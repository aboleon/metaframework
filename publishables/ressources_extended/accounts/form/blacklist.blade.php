@php
    $error = $errors->any();
@endphp
<div class="col-lg-12 mt-3 mb-3">
    <x-mfw::checkbox name="profile[blacklisted]"  :label="__('forms.fields.blacklisted')" value="1" :affected="!is_null($account?->profile?->blacklisted)"/>
</div>
<div class="col-lg-12 mb-3">
    <x-mfw::input name="profile[blacklist_comment]" :label="__('forms.fields.blacklist_comment')" :value="$error ? old('profile.blacklist_comment') : $account?->profile?->blacklist_comment"/>
</div>
