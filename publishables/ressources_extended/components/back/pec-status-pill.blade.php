@props([
    'status' => null,
])

@php
    $btnColor = match($status) {
        '1' => 'yellow',
        '2' => 'success',
        default => '',
    };

    $title = match($status) {
        '1' => 'Pec eligible but not enabled',
        '2' => 'Pec eligible AND enabled',
        default => '',
    };


@endphp

<div class="badge btn-{{$btnColor}} rounded-circle"
     title="{{$title}}"
>&nbsp;
</div>