<x-event-manager-layout :event="$event">
    <x-slot name="header">
        <h2 class="d-flex align-items-center">
            @if($edit)
                Commande #{{ $order->id }} &raquo; Modification hébérgement sur commande #{{ $amended_order->id }}
            @else
                Modification hébérgement sur commande #{{ $order->id }}
            @endif
        </h2>
        <div class="d-flex align-items-center gap-1" id="topbar-actions" x-data>
            <a href="{{ route('panel.manager.event.orders.edit', [$event->id, $amended_order->id]) }}"
               class="btn btn-success btn-sm"><i class="bi bi-arrow-left"></i> Retour sur la commande d'origine</a>
            <div class="separator"></div>
            <x-save-btns/>
        </div>

    </x-slot>

    @php
        $error = $errors->any();
    @endphp

    <x-mfw::response-messages/>
    <x-mfw::validation-errors/>

    <div class="shadow p-4 bg-body-tertiary rounded">

        <x-pec-status :event="$event"/>

        <form id="wagaia-form"
              method="post"
              action="{{ $route }}"
              autocomplete="off"
        >
            @csrf
            @if ($edit)
                @method('put')
            @endif
            @php
                $orderTotals = $orderAccessor->getOrderTotals();
            @endphp

            @push('callbacks')
                <script src="{{ asset('js/orders/generic_callbacks.js') }}"></script>
            @endpush
            @include('orders.shared.nav_tabs')
            <input type="hidden" name="is_amended_order" value="1"/>
            <input id="order_uuid"
                   data-event-id="{{ $event->id }}"
                   data-has-errors="{{ (int)$error }}"
                   type="hidden"
                   name="order_uuid"
                   value="{{ $edit ? $order->uuid : Str::uuid() }}">

            <input id="order_id"
                   type="hidden"
                   name="order_id"
                   value="{{ $order->id }}">

            <div class="tab-content mt-4" id="nav-tabContent">
                <div class="tab-pane fade show active"
                     id="order-tabpane"
                     role="tabpanel"
                     aria-labelledby="order-tabpane-tab">

                    <div class="row g-5">

                        <div class="col-lg-3">
                            <div>
                                <h4>Date</h4>
                                <div class="mfw-line-separator mt-1 mb-4"></div>
                                <x-mfw::datepicker name="order[date]"
                                                   :value="$edit ? $order->created_at->format('d/m/Y') : old('order.date', date('d/m/Y'))"/>
                            </div>
                        </div>
                        <div class="col-lg-9">

                            <div class="col-lg-4" id="client-type-selector">
                                <x-mfw::input type="hidden" name="order.client_type" :value="$order->client_type"/>
                                <x-mfw::input type="hidden" name="order.contact_id"
                                              :value="$order->client_type == 'contact' ? $order->client_id : null"/>
                                <x-mfw::input type="hidden" name="order.group_id"
                                              :value="$order->client_type == 'group' ? $order->client_id : null"/>
                            </div>

                            <div class="row" id="order-subjects">
                                <div class="col-sm-6">
                                    <h4>Compte d'affectation</h4>
                                    <div class="mfw-line-separator mt-1"></div>
                                    <div class="row mt-3">
                                        <div class="col-lg-8 text-dark" style="font-size: 16px">
                                            <small class="text-secondary">{{ $attributedTo['type'] }}</small><br>
                                            <b>{{ $attributedTo['name'] ?? 'Erreur: Commande non affectée' }}</b> <br>
                                            @if ($attributedTo['company'])
                                                <em>{{ $attributedTo['company'] }}</em><br>
                                            @endif
                                            {!! $attributedTo['address'] ?? 'Erreur: Commande non affectée' !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 text-dark">
                                    <h4>Compte payeur</h4>
                                    <div class="mfw-line-separator mt-1 mb-4"></div>

                                    @if ($samePayer)
                                        <span style="font-size: 24px;">
                                Même compte payeur
                                    </span>
                                    @else

                                        <small
                                            class="text-secondary">{{ $invoiceable['type']?? 'Erreur: Commande non affectée' }}</small>
                                        <br>
                                        <b>{{ $invoiceable['name'] ?? 'Erreur: Commande non affectée' }}</b> <br>
                                        @if (!empty($invoiceable['company']))
                                            <em>{{ $invoiceable['company'] }}</em><br>
                                        @endif
                                        {!! $invoiceable['address'] ?? 'Erreur: Commande non affectée' !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12">
                            @include('orders.shared.accommodation_cart')
                        </div>
                    </div>
                </div>
                @if ($orderAccessor->isOrder() && $orderAccessor->hasItems())
                    @include('orders.tabs.payments')
                    @include('orders.tabs.refunds')
                @endif
                @include('orders.tabs.notes')
            </div>
        </form>
    </div>
</x-event-manager-layout>
