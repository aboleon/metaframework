@if ($values)
    @foreach($values as $key => $value)
        @switch($value['type'])
            @case('textarea')
            @case('textarea_extended')
                <div class="col-12 mb-4">
                    <x-mfw-inputable::textarea name="custom[fillables][{{$key}}]"
                                :className="$value['type'] .' '.($value['class']??'') "
                                value="{!! isset($model->custom['fillables'][$key]) ? $model->custom['fillables'][$key] : '' !!}" label="{{$value['label']}}"/>
                </div>
                @break
            @default

                <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                    <x-mfw-inputable::input name="custom[fillables][{{$key}}]" value="{{ isset($model->custom['fillables'][$key]) ? $model->custom['fillables'][$key] : '' }}" label="{{$value['label']}}"/>
                </div>

        @endswitch
    @endforeach
@endif
