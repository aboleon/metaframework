@props([
    'label' => __('mfw.browser-link'),
    'route' => '#',
    'tag' => 'li',
    'with_label' => false,
    'target' => '_blank'
    ])
@if($tag)
    <{{ $tag }}>
@endif
<a href="{{ $route }}" class="mfw-browser-link btn btn-sm btn-info" data-bs-toggle="tooltip" rel="noopener" target="{{ $target }}"
   data-bs-placement="top" data-bs-title="{{ $label }}">
    <i class="bi bi-box-arrow-up-right"></i>
</a>
@if($tag)
    </{{ $tag }}>
@endif
