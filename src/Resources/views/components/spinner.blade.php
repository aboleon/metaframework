@props([
    'id' => '',
    'text' => __('mfw::mfw.loading'),
    'texthidden' => false
])

<div class="d-none mfw-spinner"{!! $id ? ' id="'.$id.'"' : '' !!}>
    <div class="spinner-border spinner-border-sm ajax-spinner" role="status"></div>
    <span{!! $texthidden ? ' class="d-none"' : ''  !!}>{{ $text }}</span>
</div>