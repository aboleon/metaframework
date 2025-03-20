@props([
    'event' => null,
    'useSep' => true,
])
<a class="btn btn-sm btn-black"
   href="{{ route('panel.events.edit', $event) }}">
    <i class="fa-solid fa-pen"></i>
    Évènement
</a>
@if($useSep)
    <x-back.topbar.separator />
@endif