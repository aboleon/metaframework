<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>
    <div class="text-center my-4">
        <a href="{{route('panel.groups.edit', $group)}}" class="btn btn-info btn-sm">Groupe</a>

    </div>
    <div>
        @php
            $error = $errors->any();
        @endphp

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-mfw::validation-banner/>
            <x-mfw::response-messages/>
            <div class="shadow p-3 mb-5 bg-body-tertiary rounded">


                <div class="row m-3">
                    <div class="col">
                        <form method="post" action="{{ $route }}">
                            @if (isset($method))
                                @method($method)
                            @endif
                            @csrf
                            <fieldset>
                                <legend>Adresse</legend>
                                <div>
                                    <x-mfw::google-places :geo="$data"/>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend>{{ __('mfw.title') }}</legend>
                                <x-mfw::input name="wa_geo[name]" :value="$error ? old('wa_geo.name') : $data->name"/>
                            </fieldset>
                            <fieldset class="mt-4">
                                <div class="fw-bold text-dark">
                                    <x-mfw::checkbox name="wa_geo[billing]" value="1" label="Il s'agit de l'adresse de facturation" :affected="$data->billing" />
                                </div>
                            </fieldset>
                            <div class="mt-5">
                                <x-mfw::btn-save/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-backend-layout>
