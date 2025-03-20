<div id="order-client-messages" data-ajax="{{ route('ajax') }}"></div>
<input type="hidden" id="selected_client_id" name="selected_client_id"
       value="{{ $error ? old('selected_client_id') : $order?->client_id }}">
<input type="hidden" id="selected_client_type" name="selected_client_type"
       value="{{ $error ? old('selected_client_type') : $order?->client_type }}">
<div class="row mt-3" id="order-client-info">
    <div class="col-lg-6">
        <div class="row">
            <div class="col-12 mb-3">
                <input type="hidden" name="payer[address_id]" id="payer_address_id"
                       value="{{ $error ? old('payer.payer_address_id') : $order->invoiceable?->address_id}}">
                <x-mfw::input name="payer.company"
                              :value="$error ? old('payer.company') : $order->invoiceable?->company"
                              label="Société"/>
            </div>
            <div class="col-sm-12 mb-3">
                <x-mfw::input name="payer.last_name"
                              :value="$error ? old('payer.last_name') : $order->invoiceable?->last_name"
                              label="Nom"/>
            </div>
            <div class="col-sm-12 mb-3">
                <x-mfw::input name="payer.first_name"
                              :value="$error ? old('payer.first_name') : $order->invoiceable?->first_name"
                              label="Prénom"/>
            </div>
            <div class="col-sm-12 mb-3">
                <x-mfw::input name="payer.department"
                              :value="$error ? old('payer.department') : $order->invoiceable?->department"
                              label="Service"/>
            </div>
            <div class="col-sm-12 mb-3">
                <x-mfw::input name="payer.vat_number"
                              :value="$error ? old('payer.vat_number') : $order->invoiceable?->vat_number"
                              label="Num TVA"/>
            </div>
        </div>
    </div>
    <div class="col-lg-6">

        <x-mfw::google-places :geo="$order->invoiceable ?: new \App\Models\Order\Invoiceable()"
                              field="payer"
                              :params="$as_orator ? [] : ['required' => ['text_address']]"
                              label="Adresse "/>
        <div class="row">
            <div class="col-sm-3">
                <x-mfw::input name="payer.cedex" :value="$error ? old('payer.cedex') : $order->invoiceable?->cedex"
                              label="Cedex"/>
            </div>
            <div class="col-sm-9">
                <x-mfw::textarea label="Complement d'adresse"
                                 height="100"
                                 name="payer[complementary]"
                                 :value="$error ? old('payer.complementary') : $order->invoiceable?->complementary"/>
            </div>
        </div>
    </div>
</div>

<x-mfw::input name="pec_enabled" type="hidden"
              :value="$orderAccessor->isOrder() && $orderAccessor->isNotGroup() ? ($event_contact['pec_enabled'] ?? '') : ''"/>

<input type="hidden" name="pec_ae" id="participation_type"
       value="{{ $orderAccessor->isOrder() ? ($event_contact['participation_type_id'] ?? '') : '' }}"/>
<input type="hidden" name="participation_type" id="participation_type"
       value="{{ $orderAccessor->isOrder() ? ($event_contact['participation_type_id'] ?? '') : '' }}"/>
<input type="hidden" name="event_group_id" id="event_group_id"
       value="{{ $orderAccessor->isOrder() ? \App\Models\EventManager\EventGroup::query()->where(['group_id' => $order->client_id, 'event_id' => $order->event_id])->value('id') : '' }}"/>


<h4>Affectation de la commande</h4>
<div id="account_info" class="mt-3 g-0 d-flex align-items-center">
    @if ($orderAccessor->isOrder())
        @switch($order->client_type)
            @case('contact')
                <a class="btn btn-secondary btn-sm" target="_blank"
                   href="{{ route('panel.manager.event.event_contact.edit', [
                            'event' => $event->id,
                            'event_contact' => \App\Models\EventContact::where(['user_id' => $order->client_id,'event_id' => $order->event_id])->value('id')
                        ]) }}">{{ $order->account->names() }}</a>

                <b class="ms-3">{{ \App\Enum\OrderClientType::translated($order->client_type) }}</b>
                @if($event_contact['pec_enabled'])
                    <span class="ms-2 fw-bold"><i class="bi bi-check-circle-fill text-success"></i> PEC</span>
                @endif
                @break

            @case('group')
                <a class="btn btn-light btn-sm ms-3 " target="_blank"
                   href="{{ route('panel.manager.event.event_group.edit', [
                            'event' => $event->id,
                            'event_group' => \App\Models\EventManager\EventGroup::where('group_id', $order->client_id)->value('id')
                        ]) }}"><i class="bi bi-pencil-fill"></i>
                    Groupe {{  $order->group->name . (!empty($order->group->company) ? ', '. $order->group->company : '')}}
                </a>
                @break
        @endswitch

        @if($orderAccessor->isGroup() && $orderAccessor->isNotFrontGroupOrder())
            <x-mfw::checkbox label="Ne pas permettre l'attribution en front"
                             class="ms-1 mt-2 text-danger"
                             name="configs.cant_attribute"
                             affected="{{ (int)($order->configs['cant_attribute'] ?? 0) }}"
                             value="1"/>
            @if ($orderAccessor->serviceCart()->isNotEmpty())
                <a class="btn btn-light btn-sm ms-3" target="_blank"
                   href="{{ route('panel.manager.event.orders.attributions', ['event' => $event, 'order' => $order, 'type'=>'service']) }}"><i
                        class="bi bi-boxes me-1"></i> Gérer les attrubutions des services</a>
            @endif
            @if ($orderAccessor->accommodationCart()->isNotEmpty())
                <a class="btn btn-light btn-sm ms-3" target="_blank"
                   href="{{ route('panel.manager.event.orders.attributions', ['event' => $event, 'order' => $order, 'type'=>'accommodation']) }}"><i
                        class="bi bi-building-fill me-1"></i> Gérer les attrubutions hébergement</a>
            @endif

        @endif
    @endif
</div>

@if($orderAccessor->isFrontGroupOrder())
    <x-warning-line warning="Cette commande groupe a été effectuée en front"/>
@endif

@if ($isSubOrder)
    <x-warning-line
        warning="Cette commande fait partie de la <a class='text-danger' href='{{ route('panel.manager.event.orders.edit', ['event' => $order->event_id, 'order' => $order->parent_id]) }}'>commande groupée #{{ $order->parent_id }}</a> d'un Groupe manager pour le compte de {{ $order->parentOrder->group->name }}"/>
@endif

@push('js')
    <script src="{{ asset('js/orders/account.js') }}"></script>
@endpush
