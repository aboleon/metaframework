<div class="btn-group" role="group">
    @forelse($values as $value => $title)
        @php
        $selected = $affected ? ($affected == $value ? 'checked' : '') : ($loop->first ? 'checked':'');
        @endphp

        <input class="btn-check" type="radio" autocomplete="off" value="{{ $value }}" name="{{ $name }}" id="{{ $name.'_'.$loop->iteration }}" {{ $selected }}>

        <label class="btn {{ $selected }}" for="{{ $name.'_'.$loop->iteration }}">
            {{ str_starts_with($title, 'trans.') ? trans(str_replace('trans.','', $title)) : $title }}
        </label>
    @empty
        {{ __('mfw::mfw.no_data_provided') }}
    @endforelse
</div>
