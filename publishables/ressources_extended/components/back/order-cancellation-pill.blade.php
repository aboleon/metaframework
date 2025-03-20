@props([
    'style' => 'none',
    'width' => 20,
    'height' => 20,
    'bgcolor' => '#b42757',
    'class' => '',
    'text' => ''
])
<div class="rounded-circle {{ $class }}"
     style="background-color: {{ $bgcolor }};width: {{ $width }}px;height: {{ $height }}px;{{ $style == 'program' ? 'font-size: 6px; position: relative; top: -2px; margin-left: 3px;' : '' }}">
    &nbsp;
</div>
@if($text)
    <small class="text-secondary d-block fw-bold">{{ $text }}</small>
@endif
