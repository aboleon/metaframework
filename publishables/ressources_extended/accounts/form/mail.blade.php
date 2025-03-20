@php
    $error = $errors->any();
@endphp


<x-mfw::input type="email" name="mails[email]" :label="__('ui.email_address')" :value="$error ? old('mails.email') : $data->email"/>
<input type="hidden" name="account_id" value="{{ $account->id }}"/>
<input type="hidden" name="callback" value="addEmailToAccountUI"/>
<br>
<div class="fw-bold text-dark">
    <x-mfw::checkbox name="mails[default]" value="1" label="Indiquer cet adresse e-mail comme principale" :affected="$data->default"/>
    <x-mfw::notice class="mt-2" message="Indiquer une adresse comme principale deviendra la nouvelle adresse de connexion au compte. La précédente sera automatiquement ajoutée aux adresses complémentaires."/>
</div>
