<div class="form-check form-check-inline">
    <input class="form-check-input"
           type="radio"
           value="{{ $value }}"
           name="{{ $name }}"
           id="{{ $id }}" {{ isset($affected) && $affected !== '' ? ($affected == $value ? 'checked' : '') : ($default && $default == $value ? 'checked' : '') }}
    @foreach($params as $param => $setting)
        @if (is_string($param))
            {{ $param }}="{!! $setting !!}"
        @else
            {!! $setting !!}
        @endif
    @endforeach
    />
    <label class="form-check-label" for="{{ $id }}">
        {{ str_starts_with($label, 'trans.') ? trans(str_replace('trans.','', $label)) : $label }}
    </label>
</div>
