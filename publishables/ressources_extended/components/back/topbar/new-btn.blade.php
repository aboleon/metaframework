@props([
    'route' => '',
    'showDevMark' => false,
])
<a class="btn btn-sm btn-success"
   href="{{ $route }}">
    <i class="fa-solid fa-circle-plus"></i>
    Créer
    @if($showDevMark)
        <x-mfw::devmark />
    @endif
</a>