@if (url()->previous() !== request()->fullUrl())
    <a class="btn btn-outline-secondary text-nowrap" href="{!! url()->previous() !!}">
        <i class="bi bi-chevron-double-left"></i>&nbsp;<span class="btn-text">{!! __('mfw::mfw.goback') !!}</span>
    </a>
@endif