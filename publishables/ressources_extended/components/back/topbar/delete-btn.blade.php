@props([
    'id' => '',
    'route' => '',
    'itemName' => null,
    'deleteBtnText' => "Supprimer",
])
@php
    $itemName = $itemName ?? "cet élément (#$id)";
@endphp
<a class="btn btn-sm btn-danger"
   href="#"
   data-bs-toggle="modal"
   data-bs-target="#destroy_{{ $id }}"
>
    <i class="fa-solid fa-trash"></i>
    {{$deleteBtnText}}
</a>

<template x-teleport="body">
    <x-mfw::modal :route="$route"
                  question="{{$deleteBtnText}} {{$itemName}} ?"
                  reference="destroy_{{ $id }}" />
</template>
