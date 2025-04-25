@if ($label)
    <label for="{{ $id }}" class="form-label">{!! $label !!}</label>
@endif

<select id="{{ $id }}"
        @if (!$disablename)
            name="{{ $name }}"
        @endif
        class="{{ $class }}"
@foreach($params as $param => $setting)
    @if (is_string($param))
        {{ $param }}="{!! $setting !!}"
    @else
        {!! $setting !!}
    @endif
@endforeach
>
    @if (is_array($values))
        @if ($nullable)
            <option value="">{{ $defaultselecttext }}</option>
        @endif
        @if ($group)
            @foreach($values as $optgroup_id => $optgroup)
                <optgroup data-id="{{ $optgroup_id }}" label="{{ $optgroup['name'] }}">
                    @foreach($optgroup['values'] as $key => $value)
                        <option data-value="{{ Str::slug($value) }}" value="{{ $key }}"{{ $affected && $key == $affected ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </optgroup>

            @endforeach
        @else
            @foreach($values as $key => $value)
                <option data-value="{{ Str::slug($value) }}" value="{{ $key }}"{{ $affected && $key == $affected ? ' selected' : '' }}>{{ $value }}</option>
                @endforeach
        @endif
    @else

        @if ($nullable)
            <option value="">{{ $defaultselecttext }}</option>
        @endif

        {!! $values !!}
    @endif
</select>

<x-mfw::validation-error :field="$validation_id"/>
