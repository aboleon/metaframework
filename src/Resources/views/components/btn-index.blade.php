@props([
    'route' => null,
])
<a class="btn btn-secondary"
   id="mfw-index-button" {!! $route ? 'href="'.$route.'"' : '' !!}>
    <i class="bi bi-list"></i> {{ __('mfw.index') }}
</a>
