@props([
    'route' => null
])
<a class="btn btn-success text-nowrap"
   id="mfw-add-button" {!! $route ? 'href="'.$route.'"' : '' !!}>
    <i class="bi bi-plus-lg"></i>&nbsp;<span class="btn-text">{{ __('mfw.add') }}</span>
</a>
