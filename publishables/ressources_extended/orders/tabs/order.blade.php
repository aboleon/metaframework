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
                                   :value="$error ? old('order.date') : ($orderAccessor->isOrder() ? $order->created_at->format('d/m/Y') : date('d/m/Y'))"/>
            </div>
        </div>
        <div class="col-lg-9">
            @include('orders.shared.order_subjects')
        </div>
    </div>

    <div @class(['d-none' => $as_orator])>
    <h4 class="mt-4">Informations payeur</h4>
    <div class="mfw-line-separator mt-1"></div>
        @include('orders.shared.payer')
    </div>

    <div class="row mt-5">
        <div class="col-12">

            @if ($orderAccessor->isOrder() && $orderAccessor->wasAmendedByAnotherOrder())
                <x-warning-line
                    warning="Cette commande a été modifiée par la <a class='text-danger' href='{{ route('panel.manager.event.orders.edit', ['event' => $order->event_id, 'order' => $order->amended_by_order_id]) }}'>commande #{{ $order->amended_by_order_id }}</a>"/>
            @endif

            <h4 class="mt-4 fs-3 fw-bold">Composition de la commande</h4>

            <div class="mfw-line-separator mt-1 mb-4"></div>

            <x-mfw::alert
                :class="'booking-lock-status '. (is_null($GLOBALS['affected_contact_id']) && is_null($GLOBALS['affected_group_id']) ? '' : 'd-none')"
                message="Vous devez sélectionner un compte d'affectation avant de pouvoir effectuer des réservations."/>

            <h4 class="mt-5">Prestations</h4>
            @include('orders.shared.service_cart')
            <div class="mfw-line-separator my-5"></div>

            <h4 class="mt-4">Hébergements</h4>
            @include('orders.shared.accommodation_cart')

        </div>

    </div>

    @include('orders.shared.order_summary')

</div>
