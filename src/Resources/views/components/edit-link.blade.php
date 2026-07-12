@props([
    'label' => __('mfw.edit'),
    'route' => '#',
    'tag' => 'li',
    'with_label' => false,
    'target' => '_self',
    ])
@if($tag)
    <{{ $tag }}>
@endif
<a href="{{ $route }}" class="mfw-edit-link btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top"
   data-bs-title="{{ $label }}" target="{{ $target }}">
    <i class="text-white bi bi-pencil-fill"></i>
</a>
@if($tag)
    </{{ $tag }}>
@endif
