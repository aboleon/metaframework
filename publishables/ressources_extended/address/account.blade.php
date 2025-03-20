<x-backend-layout>
    <x-slot name="header">
        @include('accounts.shared.subedit_header')
    </x-slot>
    @php
        $error = $errors->any();
    @endphp

    <div class="shadow p-3 mb-5 bg-body-tertiary rounded">

        <x-mfw::validation-banner/>
        <x-mfw::response-messages/>

        @if($errors->any())
            @foreach ($errors->all() as $_error)
                <div class="alert alert-danger">{{$_error}}</div>
            @endforeach
        @endif

        <div class="row m-3">
            <div class="col">
                <form method="post" action="{{ $route }}" novalidate>
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
                                Adresse<span class="text-secondary"> | {{ $account->names() }}</span>
                           </span>

                            @php
                                $url = $redirect_to ?? route('panel.accounts.edit', $account);
                            @endphp
                            <x-mfw::notice message="<a href='{{ $url }}#address-tabpane'>Adresses & Contacts</a>"/>
                        </legend>

                        <x-mfw::input name="wa_geo[name]"
                                      :value="$error ? old('wa_geo.name') : $data->name"
                                      :label="__('mfw.title')"/>

                        <div class="mt-3 mb-5 mfw-line-separator pb-5">
                            <x-mfw::input label="Raison sociale"
                                          name="wa_geo[company]"
                                          :value="$error ? old('wa_geo.company') : $data->company"/>
                        </div>


                        <x-mfw::google-places :geo="$data"
                                              label="Adresse géolocalisée (taper pour obtenir des résultats) *"/>

                        <div class="row">
                            <div class="col-sm-3">
                                <x-mfw::input name="wa_geo.cedex" :value="$error ? old('wa_geo.cedex') : $data->cedex"
                                              label="Cedex"/>
                            </div>
                            <div class="col-sm-9">
                                <x-mfw::textarea label="Complement d'adresse"
                                                 height="100"
                                                 name="wa_geo[complementary]"
                                                 :value="$error ? old('wa_geo.complementary') : $data->complementary"/>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="mt-4">
                        <div class="fw-bold text-dark">
                            <x-mfw::checkbox name="wa_geo[billing]"
                                             value="1"
                                             label="Il s'agit de l'adresse de facturation"
                                             :affected="collect($data->billing)"/>
                        </div>
                    </fieldset>
                    <div class="mt-5">
                        <x-mfw::btn-save/>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backend-layout>
