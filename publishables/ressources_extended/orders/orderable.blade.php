@php use App\Accessors\Order\Orders; @endphp
<x-event-manager-layout :event="$event">
    <x-slot name="header">
        <h2 class="d-flex align-items-center">
            {{ $orderAccessor->isOrder() ? 'Modification' : 'Création' }}
            d'une commande <span
                class="bg-warning text-dark p-2 rounded-2 ms-2">{{ $as_orator ? \App\Enum\OrderClientType::translated(\App\Enum\OrderClientType::ORATOR->value) : '' }}</span>
            @if($orderAccessor->isOrder() && Orders::ownerHasCancelled($order))
                <x-back.order-cancellation-pill class="ms-2"/>
            @endif
        </h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <x-event-config-btn :event="$event"/>
            <a class="btn btn-secondary ms-2" href="{{ route('panel.manager.event.orders.index', $event->id) }}"><i
                    class="fa-solid fa-bars"></i> Liste des commandes</a>

            <div class="separator"></div>

            <a class="btn btn-sm btn-success ms-2"
               href="{{ route('panel.manager.event.orders.create', $event->id) }}">
                <i class="fa-solid fa-circle-plus"></i>
                Commande
            </a>
            <a class="btn btn-sm btn-warning ms-2 text-dark"
               href="{{ route('panel.manager.event.orders.create', ['event' => $event->id, 'as_orator']) }}">
                <i class="fa-solid fa-circle-plus"></i>
                Commande orateur
            </a>

            <div class="separator"></div>
        </div>

    </x-slot>

    @php
        $error = $errors->any();
    @endphp

    <x-mfw::response-messages/>
    <x-mfw::validation-errors/>

    <div class="shadow p-4 bg-body-tertiary rounded">

        @if ($orderAccessor->isOrder() && $event_contact)
            <x-order-cancellation-status :order="$order" :event-contact="$event_contact"/>
        @endif

        <x-pec-status :event="$event"/>

        <form id="wagaia-form"
              data-has-errors="{{ $errors->any() }}"
              method="post"
              action="{{ $orderAccessor->isOrder() ? route('panel.manager.event.orders.update', [$event->id, $order->id]) : route('panel.manager.event.orders.store', $event->id) }}"
              autocomplete="off"
        >
            @csrf
            @if($orderAccessor->isOrder())
                @method('put')
                @php
                    $orderTotals = $orderAccessor->computeOrderTotalsFromCarts();
                @endphp
            @endif

            @push('callbacks')
                <script src="{{ asset('js/orders/generic_callbacks.js') }}"></script>
            @endpush

            <x-mfw::tab-redirect/>

            @include('orders.shared.nav_tabs')

            <input id="order_uuid"
                   data-event-id="{{ $event->id }}"
                   data-has-errors="{{ (int)$error }}"
                   type="hidden"
                   name="order_uuid"
                   value="{{ $order->uuid ?: Str::uuid() }}">
            <input type="hidden" id="as_orator" name="as_orator" value="{{ (int)$as_orator }}"/>

            <input id="order_id"
                   type="hidden"
                   name="order_id"
                   data-invoiced="{{ (int)$invoiced }}"
                   value="{{ $order->id }}">
            <div class="tab-content mt-4" id="nav-tabContent">
                @include('orders.tabs.order')
                @if ($orderAccessor->isOrder() && $orderAccessor->hasItems() && !$as_orator)
                    @include('orders.tabs.payments')
                    @include('orders.tabs.refunds')
                @endif
                @include('orders.tabs.notes')
            </div>

        </form>
    </div>
    @push('js')
        <script>
            @if ((!$error && $orderAccessor->isUnderCreation() && (!request()->has('contact') && !request()->has('group'))) or ($error && !old('selected_client_id')))
            engageBookingLock(true);
            @endif
            @if (request()->filled('tab'))
            $('button#{{ request('tab') }}').trigger('click');
            @endif
            $('#make-order').click(function (e) {
                if (produceNumberFromInput($('#as_orator').val()) === 0 && $('#order-client-info').find('.g_autocomplete').val() == '') {
                    e.preventDefault();
                    $('#order-client-messages').html('<div class="mt-2 alert alert-danger">Vous devez saisir une adresse de facturation afin de pouvoir enregistrer la commande</div>');
                    $('html, body').animate({
                        scrollTop: $('#order-client-info').offset().top - 200
                    }, 500);
                }
            });
        </script>
    @endpush
</x-event-manager-layout>
