@php
    $error = $errors->any();
@endphp
@if ($fillables)
    @foreach($fillables as $key=>$value)
        @php
            $type = $value['type'] ?? 'input';

            $array_key = $datakey ? $datakey.'['.$key.']' : $key;
            $params = $value['params'] ?? [];
            if ($disabled) {
                $params['disabled'] = true;
            }
            if ($type == 'textarea') {
                if (array_key_exists('height', $value) && (int)$value['height'] > 0) {
                    $params['height'] = (int)$value['height'];
                }
                $mode = (string)($value['mode'] ?? MetaFramework\Inputable\Enum\ContentTypeEnum::default());
            }
        @endphp

        @switch($type)
            @case('textarea')
                <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                    <x-mfw-inputable::textarea name="{{$array_key}}[{{$locale}}]"
                                               :value="$error ? old(str_replace(['[', ']'], ['.', ''], $array_key).'.'.$locale) : $model->translation($key, $locale, useFallbackLocale: $fallbacklocale )"
                                               :label="__($value['label'] ?? '')"
                                               :class="$value['class'] ?? ''"
                                               :mode="$mode"
                                               :required="in_array('required',$value)"
                                               :params="$params"/>
                </div>
                @break
            @default
                <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                    <x-mfw-inputable::input name="{{$array_key}}[{{$locale}}]"
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
