<div class="{{ rtrim('form-check' . ($switch ? ' form-switch' : '') .' '. $class) }}">
    <input class="form-check-input" type="checkbox" {!! $switch ? ' role="switch"' :'' !!} name="{{ $name }}"
           value="{{ $value }}" id="{{ $id }}"{{ $isSelected ? ' checked' : '' }}
    @foreach($params as $param => $setting)
        @if (is_string($param))
            {{ $param }}="{!! $setting !!}"
        @else
            {!! $setting !!}
        @endif
    @endforeach
    />
    @if ($label)
        <label class="form-check-label" for="{{ $id }}">
            {!! $label !!}
        </label>
    @endif
</div>
