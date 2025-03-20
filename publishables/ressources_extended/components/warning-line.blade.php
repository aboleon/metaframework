@props([
    'class' => '',
    'warning' => ''
])
<div class="fw-bold text-dark mt-2 border border-1 {{ $class }}">
    <i class="bi bi-exclamation-triangle-fill warning-icon"></i>
    {!! $warning !!}
</div>
