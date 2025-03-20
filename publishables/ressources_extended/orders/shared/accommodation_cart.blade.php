@php
    $is_amendable = isset($amendable_cart);
    $has_amended_cart = !empty($amended_cart);
    $hotels = \App\Accessors\EventManager\Accommodations::hotelLabelsWithStatus($event);
    $hotels_whitout_status = \App\Accessors\EventManager\Accommodations::hotelLabelsWithStatus($event,false);


    if ($is_amendable) {
    // Keep only the hotel_id associated to cart
        $hotels = collect($hotels)->reject(fn($item, $key) => $key != $amendable_cart->event_hotel_id)->toArray();
    }
    // Pour les modifs de lignes d'hébergements sur commandes existants, impacte l'UI classique commande
    $edit_amended = isset($edit) && $edit === true && !empty($amended_cart);
    $dates= [];
@endphp
@push('css')
    <style>
        #accommodation-selector .main {
            display: block;
            padding-bottom: 5px;
            margin-bottom: 5px;
            border-bottom: 1px dashed #adadad;
        }

        #accommodation-selector .dropdown-item:not(:last-child) {
            border-bottom: 1px solid #dee2e6;
        }

        #accommodation-selector .dropdown-item {
            cursor: pointer;
        }

        #accommodation-selector .dropdown-toggle:after {
            display: none;
        }

        #accommodation-selector span:not(.main) {
            font-size: 14px;
        }

        .dropdown-item.active, .dropdown-item:active {
            background-color: var(--bs-dropdown-link-hover-bg);
            color: initial;
        }

        #accommodation-cart td {
            vertical-align: top;
        }

        #accommodation-cart span.hotel .status {
            display: none !important;
        }

    </style>
@endpush


@if ($is_amendable)
    <h4 class="mt-4 fs-3 fw-bold">Modification de la réservation</h4>
@endif

@include('orders.shared.accommodation-selector')

@if ($is_amendable or $has_amended_cart)
    @include('orders.shared.amendable-accommodation-cart')
@endif

@if ($orderAccessor->isFrontGroupOrder())

    {{-- Recap pour les paniers achetés en front depuis un group manager --}}
    @php
        $accommodationSuborders = $order->suborders->load('accommodation','account','taxRoom');
    @endphp
    @if ($accommodationSuborders->contains(function ($suborder) {
            return $suborder->accommodation->isNotEmpty();
        }))
        @include('orders.shared.accommodation_cart-front-group-order')
    @else
        <x-mfw::alert message="Aucune réservation d'hébergement dans cette commande"/>
    @endif
@else
    @include('orders.shared.accommodation_cart-regular')

@endif
