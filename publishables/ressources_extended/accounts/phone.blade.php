<x-backend-layout>
    <x-slot name="header">
        @include('accounts.shared.subedit_header')
    </x-slot>
    @php
        $error = $errors->any();
    @endphp
    <div class="shadow p-3 mb-5 bg-body-tertiary rounded">

        <x-mfw::validation-banner />
        <x-mfw::response-messages />

        <div class="row m-3">
            <div class="col">
                <form method="post" action="{{ $route }}">
                    @if (isset($method))
                        @method($method)
                    @endif
                    @csrf

                    @if($redirect_to)
                        <input type="hidden" name="custom_redirect" value="{{ $redirect_to }}">
                    @endif

                    <fieldset class="position-relative">
                        <legend class="d-flex justify-content-between align-items-end">
                            <span>
                                Numéro de téléphone<span class="text-secondary"> | {{ $account->names() }}</span>
                           </span>
                            @php
                                $url = $redirect_to ?? route('panel.accounts.edit', $account);
                            @endphp
                            <x-mfw::notice message="<a href='{{ $url }}#address-tabpane'>Adresses & Contacts </a>" />
                        </legend>

                        <x-mfw::input name="phone[name]"
                                      :label="__('ui.title')"
                                      :value="$error ? old('phone.name') : $data->name" />

                        <br>

                        <x-mfw::input name="phone[phone]"
                                      :label="__('account.phone')"
                                      :value="$error ? old('phone.phone') : $data->phone" />

                        <br>
                        <x-mfw::select :label="__('mfw.geo.country_code')"
                                       name="phone[country_code]"
                                       :values="\MetaFramework\Accessors\Countries::orderedCodeNameArray()"
                                       :affected="$error ? old('phone.country_code') : ($data->country_code ?? 'FR')"
                                       :nullable="false" />

                        <br>
                        <div class="fw-bold text-dark">
                            <x-mfw::checkbox name="phone[default]"
                                             value="1"
                                             label="Il s'agit du numéro de téléphone principal"
                                             :affected="$data->default" />
                        </div>

                    </fieldset>
                    <div class="mt-5">
                        <x-mfw::btn-save />
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backend-layout>
