@push('css')
    <style>
        .service-item > .d-flex {
            display: block;
            margin-bottom: 5px;
            border-bottom: 1px dashed #adadad;
            align-items: center;
        }

        .service-grouped > .row > div {
            border: 1px solid #dee2e6;
            background: white;
            width: 49%;
            margin: 5px;
            border-radius: 4px;
            padding: 10px;
        }

        select.to-participant {
            margin-top: 4px;
            font-size: 13px;
        }

        #service-selector li {
            cursor: pointer;
        }

        #service-selector .dropdown-toggle:after {
            display: none;
        }

        #service-selector span:not(.main) {
            font-size: 14px;
        }

        li.active, li:active {
            background-color: var(--bs-dropdown-link-hover-bg);
            color: initial;
        }

        .dropdown-menu {
            position: initial;
        }

        .bluie {
            color: #41739f;
            font-weight: 500;
            display: inline-block;
            border-bottom: 1px dotted #41739f;
        }

        #account_info {
            border: 1px dotted #41739f;
            padding: 10px;
        }
    </style>
@endpush

@php
    $eventServices = $event->services->mapWithKeys(fn($item) => [$item->id => ['max' => $item->max, 'name' => $item->name, 'unlimited'=> $item->unlimited]]);
    $services = $event->sellableService->load('event.services');
@endphp

{{-- Les différents blocs de prestations disponibles pour la commande --}}
@include('orders.shared.service_selector')

@if ($orderAccessor->isFrontGroupOrder())
    {{-- Recap pour les paniers achetés en front depuis un group manager --}}
    @php
        $serviceSuborders = $order->suborders->load('services','account');
    @endphp
    @if ($serviceSuborders->contains(function ($suborder) {
            return $suborder->services->isNotEmpty();
        }))
        @include('orders.shared.service_cart_lines_front_group_order')
    @else
        <x-mfw::alert message="Aucune prestation dans cette commande"/>
    @endif
@else
    {{-- Panier classique BO --}}
    @include('orders.shared.service_cart_lines')

    @pushonce('callbacks')
        <script src="{{ asset('js/orders/service_cart_callbacks.js') }}"></script>
    @endpushonce
    @pushonce('js')
        <script src="{{ asset('js/orders/service_cart.js') }}"></script>
    @endpushonce

@endif
