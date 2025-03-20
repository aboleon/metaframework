@props([
    'name',
    'value' => '',
    'label' => '',
    'class' => '',
    'xMask' => '',
    'required' => false,
    'params' => [],
])

@php
    $params = array_merge([
        'x-mask' => $xMask,
        'autocomplete' => 'off',
        'x-data' => '{}',
    ], $params);
@endphp
    <x-mfw::input :name="$name"
                  :value="$value"
                  :label="$label"
                  :class="$class"
                  :required="$required"
                  :params="$params" />

