<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configuration
        </h2>
    </x-slot>

    @php
        $error = $errors->any();
    @endphp

    <x-mfw-support::validation-errors/>
    <x-mfw-support::response-messages/>
    <form method="post" action="{{ route('mfw.settings.update') }}">
        @csrf
        <div class="row editable">
            @foreach($config_settings as $config_setting)
                <div class="col-md-12">
                    <div class="bloc-editable">
                        <h2>{{ $config_setting['title'] }}</h2>
                        @foreach($config_setting['elements'] as $item)
                            @php
                                $value = $error
                                ? old(request('mfw-settings.'.$item['name']))
                                : (MetaFramework\Models\Setting::value($item['name'])
                                    ?: \MetaFramework\Models\Setting::defaultSettingValue($item['name']));
                            @endphp
                            @if($item['type'] == 'textarea')
                                <x-mfw-inputable::textarea name="{{ $item['name'] }}"
                                                 class="{{  $item['class'] ?? ''}}"
                                                 :label="$item['title'] ?? ''"
                                                 :value="$value"/>
                                @once
                                    @include('mfw::lib.tinymce')
                                @endonce
                            @else
                                <x-mfw-inputable::input name="{{$item['name']}}"
                                              type="{{ $item['type'] }}"
                                              label="{!! $item['title'] ?? '' !!}"
                                              class="{{ $item['class'] ?? '' }}"
                                              value="{!! $value  !!}"/>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <x-mfw::btn-save/>
    </form>
</x-backend-layout>
