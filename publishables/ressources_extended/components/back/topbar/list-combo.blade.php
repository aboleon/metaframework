@props([
    'routePrefix' => '',
    'createRoute' => null,
    'wrap' => true,
    'createBtnDevMark' => false,
    'showCreateRoute' => true,
    'event' => null,
])
@if($wrap)
    <div class="d-flex align-items-center gap-1" id="topbar-actions">
        @endif


        @if($event)
            <x-back.topbar.edit-event-btn :event="$event" />
        @endif

        @if($showCreateRoute)
            @php
                $createRoute = $createRoute ?? route($routePrefix . '.create');
            @endphp
            <x-back.topbar.new-btn :route="$createRoute" :show-dev-mark="$createBtnDevMark" />
            <x-back.topbar.separator />
        @endif
        @if($wrap)
    </div>
@endif