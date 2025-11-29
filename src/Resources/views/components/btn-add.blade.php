@props([
    'route' => null,
])
<a class="btn btn-success"
   id="mfw-index-button" {!! $route ? 'href="'.$route.'"' : '' !!}>
    <i class="bi bi-plus-lg"></i> {{ __('mfw.add') }}
</a>
