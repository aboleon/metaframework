@props([
    'route' => null,
])
<a class="btn btn-success"
   id="mfw-add-button" {!! $route ? 'href="'.$route.'"' : '' !!}>
    <i class="bi bi-plus-lg"></i> {{ __('mfw.add') }}
</a>
