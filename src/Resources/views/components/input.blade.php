@if ($label)
    <label for="{{ $id }}" class="form-label">{!! $label . ($required ? ' *' : '') !!}</label>
@endif

@if ($prefix)
    <div class="input-group">
        <span class="input-group-text" id="basic-addon-{{ $id }}">{!! $prefix !!}</span>
        @endif

        <input type="{{ $type ?? 'text' }}"
               name="{{ $name }}"
               class="form-control {{ $class ?? ''  }}"
               id="{{ $id }}"
               value="{{ $value }}"
        @foreach($params as $param => $setting)
            @if (is_string($param))
                {{ $param }}="{!! $setting !!}"
            @else
                {!! $setting !!}
            @endif
        @endforeach
        @if($required)
            required
        @endif
        @if($readonly)
            readonly
        @endif
        >
        @if ($prefix)
    </div>
@endif
<x-mfw::validation-error :field="$validation_id"/>
