@php
    $error = $errors->any();
@endphp
@if ($fillables)
    @foreach($fillables as $key=>$value)
        @php
            $array_key = $datakey ? $datakey.'['.$key.']' : $key;
            $params = $value['params'] ?? [];
            if ($disabled) {
                $params['disabled'] = true;
            }
        @endphp

        @switch($value['type'] ?? '')
            @case('textarea')
                <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                    <x-mfw-input::textarea name="{{$array_key}}[{{$locale}}]"
                                     :value="$error ? old(str_replace(['[', ']'], ['.', ''], $array_key).'.'.$locale) : $model->translation($key, $locale, useFallbackLocale: $fallbacklocale )"
                                     :label="__($value['label'] ?? '')"
                                     :class="$value['class'] ?? ''"
                                     :required="in_array('required',$value)"
                                     :params="$params"/>
                </div>
                @break
            @default
                <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                    <x-mfw-input::input name="{{$array_key}}[{{$locale}}]"
                                  :value="$error ? old(str_replace(['[', ']'], ['.', ''], $array_key).'.'.$locale) : $model->translation($key, $locale, useFallbackLocale: $fallbacklocale)"
                                  :label="__($value['label'] ?? '')"
                                  :required="in_array('required',$value)"
                                  :params="$params"/>
                </div>
        @endswitch
    @endforeach
@else
    @if ($parsed)
        <x-mfw-support::alert
            message="A parse was attempted on {!! implode(', ',array_map(fn($item) => '<em>\''.$item.'\'</em>', $parsed) ) !!}"
            type="warning"/>
    @endif
@endif
