@if ($values)
    @foreach($values as $key => $value)
        @switch($value['type'])
            @case('textarea')
            @case('textarea_extended')
                <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                    <x-mfw-inputable::textarea name="custom[translatables][{{$key}}][{{$locale}}]" :className="$value['type'] .' '.($value['class']??'') " value="{!! isset($model->custom['translatables'][$key][$locale]) ? $model->custom['translatables'][$key][$locale] : '' !!}" label="{{$value['label']}}"/>
                </div>
                @break
            @default
                <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                    <x-mfw-inputable::input name="custom[translatables][{{$key}}][{{$locale}}]" value="{{ isset($model->custom['translatables'][$key][$locale]) ? $model->custom['translatables'][$key][$locale] : '' }}" label="{{$value['label']}}"/>
                </div>
        @endswitch
    @endforeach
@endif
