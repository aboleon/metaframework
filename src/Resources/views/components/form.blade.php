@php
    $config = collect(config('mfw-forms'))->where('name', $form->name)->first();
@endphp


@if (is_null($config))
    @if (config('app.debug'))
        Le formulaire {{ $form->name }} n'existe pas dans les configurations
    @endif
@else

    <form class="form-{{ $form->name }}" action="{{ route('form', $form->name) }}" method="post" id="form-{{ $form->name }}">
        @csrf
        <h3>{!! $label ?: __('mfw-forms.labels.'.$form->name) !!}</h3>

        <x-mfw::validation-errors/>
        <x-mfw::response-messages/>

        <div class="row">
            @foreach($config['fields'] as $field)
                <div class="col {{ $field['grid'] }} mb-3">
                    @php
                        $use_as_label = $field['label'] ?? $field['name'];
                        $label = array_key_exists($use_as_label, trans('forms')) ?  __('mfw-forms.'.$use_as_label) : $use_as_label;
                    @endphp
                    @switch($field['type'])
                        @case('textarea')
                            <x-mfw-input::textarea :name="$field['name']" :label="$label" :value="old($field['name'])"/>
                            @break
                        @case('radio')
                            <x-mfw-input::radio :name="$field['name']" :label="$label" :values="$field['values']" :affected="old($field['name'])"/>
                            @break
                        @case('btn-group')
                            <x-mfw::btn-group :name="$field['name']" :label="$label" :values="$field['values']" :affected="old($field['name']) ?: $field['default']"/>
                            @break
                        @case('checkbox')
                            <x-mfw-input::checkbox :name="$field['name']" :label="$label" value="1" :affected="null"/>
                            @break
                        @case('select')
                            <x-mfw-input::select :name="$field['name']" :label="$label" :values="$field['values']" :affected="null" />
                            @break
                        @default
                            <x-mfw-input::input type="{{ $field['type'] }}" :name="$field['name']" :label="$label" :value="old($field['name'])" autocomplete="off"/>
                    @endswitch
                </div>
            @endforeach
            <div class="col text-center {{ $config['submit']['grid'] ?? 'col-12' }}">
                <button form="form-{{ $form->name }}" type="submit">{{ $btn ?: __('mfw-forms.save') }}</button>
            </div>
        </div>
    </form>
@endif
