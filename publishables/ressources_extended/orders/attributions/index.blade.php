<x-event-manager-layout :event="$event">
    <x-slot name="header">
        <h2>
            Gestion des commandes > Attributions {{ \App\Enum\OrderCartType::translated($type) }}
        </h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-secondary mx-2"
               href="{{ route('panel.manager.event.orders.edit', [$event->id, $order->id]) }}">
                <i class="fa-solid fa-arrow-left"></i>
                Retour à la commande</a>
            <a class="btn btn-sm btn-success mx-2"
               href="{{ route('panel.manager.event.orders.create', $event) }}">
                <i class="fa-solid fa-circle-plus"></i>
                Créer</a>
            <a class="btn btn-sm btn-warning"
               href="{{ route('panel.manager.event.show', $event) }}"
               style="color: black">
                <i class="bi bi-bounding-box"></i>
                Gestion de l'évènement</a>
            <div class="separator"></div>
        </div>
    </x-slot>

    @push('css')
        @php
        $mcount = $groupMembers->count() > 20 ? 2 : 1;
        @endphp
        <style>
            .members-list {
                -webkit-column-count: {{ $mcount }};
                -moz-column-count: {{ $mcount }};
                column-count: {{ $mcount }};
            }
        </style>
    @endpush

    @section('meta_title')
        Gestion des commandes > Attributions
    @endsection

    <div class="shadow p-4 bg-body-tertiary rounded">

        @if ($error)

            <x-mfw::alert :message="$error" />

        @else

            <x-mfw::response-messages/>
            <form action="" id="attributions-form"
                  data-origin="{{ \App\Enum\OrderOrigin::BACK->value }}"
                  data-event-id="{{ $event->id }}"
                  data-group-id="{{ $order->client_id }}"
                  data-order-id="{{ $order->id }}">
                <div class="row">
                    <div class="col-sm-9">
                        <h4 class="fs-3 fw-bold">
                            Attributions des éléments de commande aux membres du groupe
                            <b>{{ $group->name }}</b>
                        </h4>
                    </div>
                    <div class="col-sm-3 text-end">
                        <a class="btn btn-sm btn-secondary"
                           href="{{ route('panel.manager.event.orders.edit', ['event' => $event, 'order' => $order]) }}">Retour
                            à la commande #{{ $order->id }}</a>
                    </div>
                </div>
                <div class="mfw-line-separator mb-4 pb-2"></div>

                @if (!in_array($type, [App\Enum\OrderCartType::SERVICE->value, App\Enum\OrderCartType::ACCOMMODATION->value]))
                    <x-mfw::notice message="Vous pouvez gérer uniquement des attribution de service ou hébergements"/>
                @else
                    @include('orders.attributions.'.$type)
                @endif
            </form>

        @endif
    </div>

    @include('orders.attributions.messages')

    @push('callbacks')
        <script>
            function postCreateAttributions(result) {
                if (result.hasOwnProperty('error')) {
                    return;
                }
                setTimeout(function () {
                    for (const [key, values] of Object.entries(result.stored)) {
                        // Iterate over each item in the stored array
                        values.forEach(item => {
                            let selector = '.affected-service.' + result.type + '-' + key;
                            let element = $(selector).find('a');

                            // Set data attributes
                            element.attr('data-model-id', item.id);
                            element.attr('data-identifier', result.type + '-' + key);
                        });
                    }
                }, 500);
            }
        </script>
        <script src="{{ asset('js/orders/order_attribution.class.js') }}"></script>
    @endpush

</x-event-manager-layout>
