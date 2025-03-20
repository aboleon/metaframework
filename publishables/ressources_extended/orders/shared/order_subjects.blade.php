@php
    use App\Enum\OrderClientType;
        $selectableEventContacts = $as_orator ? $event->contacts->whereIn('participation_type_id', \App\Accessors\Dictionnaries::oratorsIds()) : $event->contacts;
        $GLOBALS['affected_contact_id'] = $error ? old('order.contact_id') : ($orderAccessor->isNotGroup() ? $order->client_id : null);
        $GLOBALS['affected_group_id'] = $error ? old('payer.group_id') : ($orderAccessor->isGroup() ? $order->client_id : null);
        $contacts = \App\Models\Account::query()->whereIn('id', $selectableEventContacts->pluck('user_id'))
                        ->selectRaw('id, concat_ws(" ",UPPER(last_name), first_name) as name')->orderBy('last_name')->pluck('name','id')->toArray();
        $groups = $event->groups->mapWithKeys(fn($item) => [$item->id => $item->name .($item->company ? ', '.$item->company : '')])->sort()->toArray();
@endphp
@if ($invoiced)
    <style>
        #order-subjects > div > div {
            opacity: 0.6
        }
    </style>
@endif
<div class="row" id="order-subjects">
    <div class="col-sm-6">
        <h4>Compte d'affectation</h4>
        <div class="mfw-line-separator mt-1"></div>
        <div class="row mt-3">
            <div @class([
            'col-lg-4',
            'd-none' => $as_orator]) id="client-type-selector">
                <x-mfw::radio
                    name="order.client_type"
                    :values="['contact' => 'Participant', 'group' => 'Groupe']"
                    :affected="$error ? old('order.client_type') : ($orderAccessor->isNotGroup() ? OrderClientType::CONTACT->value : ($order->client_type ?? OrderClientType::CONTACT->value))"
                />

            </div>
            <div class="col-lg-8" id="client-type-subselector">
                <div id="order_affectable_contact"
                     class="{{ $error && old('order.client_type') == 'group' ? 'd-none' : (($orderAccessor->isNotGroup() or !$order->client_type) ? '' : 'd-none') }}">
                    <x-mfw::select name="order.contact_id"
                                   :values="$contacts"
                                   :affected="$GLOBALS['affected_contact_id']"/>
                </div>
                <div id="order_affectable_group"
                     class="{{ $error && old('order.client_type') == 'group' ? '' : ($orderAccessor->isGroup() ? '' : 'd-none') }}">
                    <x-mfw::select name="order.group_id"
                                   :values="$groups"
                                   :affected="$GLOBALS['affected_group_id']"/>
                </div>
            </div>
        </div>
    </div>

    <div @class([
            'col-lg-6',
            'd-none' => $as_orator])>
        <h4>Compte payeur</h4>
        <div class="mfw-line-separator mt-1 mb-3"></div>
        <div class="mb-2" id="external_invoice">
            <x-mfw::checkbox label="Facturation externe" value="1" name="order.external_invoice"
                             :affected="$error ? (bool)old('order.external_invoice') : (bool)$order->external_invoice"/>

            <x-mfw::checkbox name="samepayer" value="1" :switch="true" label="Même compte payeur"
                             :affected="(int)$samePayer"/>

            <div id="payer_container" class="row mt-3 {{ $samePayer ? 'd-none' : '' }}">
                <div class="col-lg-4" id="payer-type-selector">
                    <x-mfw::radio name="payer.account_type"
                                  :values="['contact' => 'Participant', 'group' => 'Groupe']"
                                  :affected="$error
                                  ? old('payer.account_type')
                                  : ($order->invoiceable?->account_type ?: 'contact')
                                  "/>
                </div>
                <div class="col-lg-8" id="payer-type-subselector">
                    <div id="payable_contact"
                         class="{{ $error && old('payer.account_type') == 'group' ? 'd-none' : (($order->invoiceable?->account_type == 'contact' or !$order->invoiceable?->account_type) ? '' : 'd-none') }}">
                        <x-mfw::select name="payer.contact_id"
                                       :values="$contacts"
                                       :affected="$error
                                       ? old('payer.contact_id')
                                       : ($order->invoiceable?->account_type == 'contact' ? $order->invoiceable?->account_id : null)
                                       "/>
                    </div>

                    <div id="payable_group"
                         class="{{ $error && old('payer.account_type') == 'group' ? '' : ($order->invoiceable?->account_type == 'group' ? '' : 'd-none') }}">
                        <x-mfw::select name="payer.group_id"
                                       :values="$groups"
                                       :affected="$error
                                       ? old('payer.group_id')
                                       : ($order->invoiceable?->account_type == 'group' ? $order->invoiceable?->account_id : null)
                                       "/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('lib.select2')

@if ($orderAccessor->isOrder())
    <x-mfw::alert class="{{ $as_orator ? 'mt-3' : '' }}"
                  type="warning"
                  message="Les changements du compte d'affectation et du compte payeur ne sont pas autorisés."/>
    @push('js')
        <script>
            $(function () {
                let c = $('#order-subjects'), clientTypeSelected = $('#client-type-selector :checked'),
                    clientType = $('#order_' + clientTypeSelected.val() + '_id');

                console.log(clientType.val());
                c.append('<input type="hidden" name="' + clientTypeSelected.attr('name') + '" value="' + clientTypeSelected.val() + '" />');
                c.append('<input type="hidden" name="' + clientType.attr('name') + '" value="' + clientType.val() + '" />');

                let payerTypeSelected = $("#payer-type-selector :checked"),
                    payerType = $('#payer_' + payerTypeSelected.val() + '_id');
                c.append('<input type="hidden" name="samepayer" value="1" />');
                c.append('<input type="hidden" name="' + payerTypeSelected.attr('name') + '" value="' + payerTypeSelected.val() + '" />');
                c.append('<input type="hidden" name="' + payerType.attr('name') + '" value="' + payerType.val() + '" />');

                let external_invoicing = $('#external_invoice input');

                external_invoicing.click(function () {
                    let selectors = $('#payment-container, #invoice-cancel-container');
                    $(this).is(':checked') ? selectors.hide() : selectors.show();
                });

                $('#order-subjects select.form-control').select2();

                @if ($invoiced)
                $('#order-subjects').find('input, select').on('click change mousedown', function (e) {
                    e.preventDefault();
                });
                $('#order-subjects select').on('select2:opening', function (e) {
                    e.preventDefault();
                });

                @endif
            });
        </script>
    @endpush
@endif
