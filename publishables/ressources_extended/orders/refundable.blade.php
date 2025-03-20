@php use App\Accessors\Order\Orders; @endphp
<x-event-manager-layout :event="$event">
    <x-slot name="header">
        <h2>
            {{ $order->id ? 'Modification' : "Création" }} d'un avoir

        </h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-warning mx-2"
               style="color:black"
               href="{{ route('panel.manager.event.orders.edit',['event' => $event, 'order' => $order]) }}?tab=refunds-tabpane-tab">
                <i class="fa-solid fa-arrow-left"></i>
                Retour sur la commande</a>
            <a class="btn btn-sm btn-secondary mx-2"
               href="{{ route('panel.manager.event.orders.index', $event) }}">
                <i class="fa-solid fa-bars"></i>
                Commandes</a>
            <x-event-config-btn :event="$event"/>
            <div class="separator"></div>
            <x-save-btns/>
        </div>
    </x-slot>

    @php

        $error = $errors->any();
        if ($errors->any()) {
            if (old('order_refund')) {
                $d = old('order_refund');

            $refunds = collect();
                for($i=0;$i<count($d['date']);$i++) {

                    $refunds->push(new \App\Models\Order\RefundItem([
                        'amount' => $d['amount'][$i],
                        'object' => $d['object'][$i],
                        'vat_id' => $d['vat_id'][$i],
                        'date' => $d['date'][$i]
                    ])
                    );
                }
            }
        }
    @endphp
    @if (isset($error_message))
        <x-mfw::alert :message="$error_message" class="simplified"/>
    @endif
    <x-mfw::response-messages/>
    <x-mfw::validation-errors/>

    <form action="{{ $route }}" id="wagaia-form" method="post">
        @csrf
        @method($method)

        <div class="shadow p-4 bg-body-tertiary rounded">
            <h4>Création d'un avoir</h4>
            <table id="refunds" class="table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Objet</th>
                    <th>Montant TTC</th>
                    <th>Taux TVA</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($refunds as $model)
                    <x-refund-row :model="$model"/>
                @empty
                @endforelse
                </tbody>
            </table>

            <button type="button" class="btn btn-sm btn-success mt-2" id="add-refund">Ajouter une ligne</button>
        </div>
    </form>

    <template id="refunds-template">
        <x-refund-row :model="new \App\Models\Order\RefundItem()"/>
    </template>

    @push('js')
        <script>
                function removeRefundRow() {
                    $('.delete_refund_row').off().on('click', function () {
                        $('tr.refund_row[data-identifier=' + $(this).attr('data-identifier')+']').remove();
                        $(this).prev('button').trigger('click');
                    });
                }

                const refunds = {
                    container: function () {
                        return $('table#refunds');
                    },
                    template: function () {
                        return $($('#refunds-template').html())
                    },
                    appendRow: function (row) {
                        this.container().find('tbody').append(row);
                    },
                    addRow: function () {
                        let row = this.template(),
                            uniqueId  = guid();
                        $(row).attr('data-identifier', uniqueId );
                        $(row).find('a').attr('data-identifier', uniqueId);

                        this.appendRow(row);
                    },
                    calculator: function () {
                        $('input.amount').off().on('keyup', function () {
                            let amount = produceNumberFromInput($(this).val()),
                                vat_rate = $(this).closest('tr').find('select').val();
                            setDelay(function () {

                            }, 500);
                        });
                    },
                    add: function () {
                        $('#add-refund').click(function () {
                            refunds.addRow();
                            setDatepicker();
                        });
                    },
                    init: function () {
                        this.add();
                    }
                }
                refunds.init();
        </script>
        @if($method == 'post' && $refunds->count() < 1)
            <script>
                    $('#add-refund').click();
            </script>
        @endif
    @endpush
</x-event-manager-layout>
