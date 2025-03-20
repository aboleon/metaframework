@php
    use App\Enum\PaymentMethod;
@endphp
<div class="tab-pane fade"
     id="payments-tabpane"
     role="tabpanel"
     aria-labelledby="payments-tabpane-tab">
    @if (!$orderAccessor->isOrder())
        Veuillez enregistrer la commande pour ajouter des paiements.
    @else
        @if ($order->external_invoice)
            Facturation externe
        @else
            <div class="container" id="payment-container">
                <div class="row g-5">
                    <div class="col">
                        <h4>Paiements</h4>
                            <?php
                            $orderTotals = $orderAccessor->getOrderTotals();

                            $paymentMethods = PaymentMethod::translations();
                            $toPay          = ($orderTotals['net'] + $orderTotals['vat']);
                            $totalPaid      = $order->payments->sum('amount');
                            ?>
                        <div id="payment-ajax-container" data-ajax="{{ route('ajax') }}">

                            <div class="messages"></div>
                            <table class="table table-bordered table-payment">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Montant TTC</th>
                                    <th>Paiement</th>
                                    <th>Num auto</th>
                                    <th>Num carte</th>
                                    <th>Banque</th>
                                    <th>Emetteur</th>
                                    <th>Chèque</th>
                                    <th class="invoiced {{ $invoiced ? 'd-none' : '' }}">Actions</th>
                                </tr>
                                </thead>
                                <tbody id="payment-rows"></tbody>
                            </table>
                            @php
                                $is_paid = $orderAccessor->isFullyPaid();
                            @endphp
                            <div class="row">
                                <div class="col-sm-6">
                                    <div>
                                        <button
                                            class="btn btn-default btn-sm btn-add-row addPaymentBtn invoiced {{ $is_paid ? 'd-none' : '' }}">
                                            +
                                            Ajouter
                                        </button>
                                        <x-mfw::simple-modal id="issue_invoice_proforma"
                                                             class="btn btn-secondary btn-sm btn-download-proforma invoiced {{ $invoiced ? 'd-none' : '' }}"
                                                             title="Edition d'une facture proforma"
                                                             confirm="Éditer"
                                                             body="Cette action éditera une facture proforma avec les paiements effectués et le restant à régler."
                                                             callback="ajaxIssueInvoiceProforma"
                                                             text='<i class="fas fa-print me-1"></i> Facture Proforma'/>
                                        <x-mfw::simple-modal id="issue_invoice"
                                                             class="btn btn-success btn-lg mt-1 invoiceBtn invoiced {{ !$invoiced && $is_paid ? '' : 'd-none' }}"
                                                             title="Edition d'une facture"
                                                             confirm="Facturer"
                                                             body="Cette action verrouillera la commande et éditera une facture."
                                                             callback="ajaxIssueInvoice"
                                                             text='Facturer'/>
                                    </div>
                                    <div id="invoice_proforma_files" class="mt-4">
                                        <h6 class="fw-bold text-dark">Factures proforma</h6>
                                        <table class="table-compact text-dark">
                                            @if ($order->proforma()->isNotEmpty())
                                                <tbody>
                                                @foreach($order->proforma() as $proforma)
                                                    <tr style="border-top: 1px solid #ccc">
                                                        <td><b>Date :</b> {{ $proforma->created_at->format('d/m/Y') }}
                                                        </td>
                                                        <td>
                                                            <a class="btn" target="_blank"
                                                               href="{!! route('pdf-printer', ['type' => 'invoice', 'identifier' => $order->uuid, 'proforma' => $proforma->id])!!}">Visualiser
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a class="btn" target="_blank"
                                                               href="{!! route('pdf-printer', ['type' => 'invoice', 'identifier' => $order->uuid, 'proforma' => $proforma->id,'download'])!!}">Télécharger
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            @else
                                                <tfoot>
                                                <tr>
                                                    <td>Aucune proforma éditée pour cette commande</td>
                                                </tr>
                                                </tfoot>

                                            @endif
                                        </table>
                                    </div>
                                    <div id="invoice_files" class="mt-3 {{ $invoiced ? '' : 'd-none' }}">

                                        <h6 class="fw-bold text-dark">Factures </h6>

                                        <table class="table-compact text-dark">
                                            <tr style="border-top: 1px solid #ccc">
                                                <td class="invoice-date"><b>Date
                                                        :</b> {{ $order->invoice()?->created_at?->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    <a class="btn" target="_blank"
                                                       href="{!! route('pdf-printer', ['type' => 'invoice', 'identifier' => $order->uuid])!!}">Visualiser
                                                        la
                                                        facture</a>
                                                </td>
                                                <td>
                                                    <a class="btn" target="_blank"
                                                       href="{!! route('pdf-printer', ['type' => 'invoice', 'identifier' => $order->uuid, 'download'])!!}">Télécharger
                                                        la facture</a>
                                                </td>
                                                <td>
                                                    <div class="d-inline-block"
                                                         data-bs-placement="top"
                                                         data-bs-title="Envoyer par e-mail"
                                                         data-bs-toggle="tooltip">
                                                        <a href="#" class="btn btn-sm btn-secondary"
                                                           data-bs-toggle="modal"
                                                           data-bs-target="#send_by_mail_{{ $order->id }}"
                                                        ><i class="fas fa-envelope"></i> Envoyer la facture</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>

                                        <x-mfw::modal
                                            :route="route('panel.mailer', ['type' => 'invoice', 'identifier' => $order->uuid])"
                                            title="Envoyer la facture par e-mail ?"
                                            :params="['uuid' => $order->uuid]"
                                            class="sendinvoicebymail"
                                            question=""
                                            reference="send_by_mail_{{ $order->id }}"/>

                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <td class="text-end left-cell">Montant à payer</td>
                                            <td class="text-end" id="payments-total"
                                                data-amount="{{ number_format($toPay, 2) }}">{{ \MetaFramework\Accessors\Prices::readableFormat($toPay) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-end left-cell">Total payé</td>
                                            <td class="text-end" id="payments-paid"
                                                data-amount="{{ number_format($totalPaid, 2) }}">{{ \MetaFramework\Accessors\Prices::readableFormat($totalPaid) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-end left-cell">Reste à payer</td>
                                            <td class="text-end" id="payments-remaining"
                                                data-amount="{{ number_format($totalPaid - $toPay, 2) }}">{{ \MetaFramework\Accessors\Prices::readableFormat($toPay - $totalPaid) }}
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="invoice-texts" class="mt-4">
                                <h6 class="fw-bold text-dark">Informations complémentaires sur la facturation</h6>
                                <hr/>
                                <div class="row">
                                    <div class="col-12 pb-3">
                                        <x-mfw::input name="order.po" :value="$error ? old('order.po') : $order->po"
                                                      label="PO"/>
                                    </div>
                                    <div class="col-sm-6">
                                        <x-mfw::textarea name="order.note"
                                                         :value="$error ? old('order.note') : $order->note"
                                                         label="Commentaires"/>
                                    </div>
                                    <div class="col-sm-6">
                                        <x-mfw::textarea name="order.terms"
                                                         :value="$error ? old('order.terms') : $order->terms"
                                                         label="Termes de paiement "/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @include('orders.shared.payment_templates')

                        @push('css')
                            <style>
                                .left-cell {
                                    background: #f7f1ec !important;
                                }
                            </style>
                        @endpush

                        @push('js')
                            <script>
                                function manageInvoiceableStatus(result) {
                                    if (!result.hasOwnProperty('error')) {
                                        if (getInvoiceableAmountToPay() === produceNumberFromInput(result.paid)) {
                                            $('.invoiceBtn').removeClass('d-none');
                                            $('.addPaymentBtn, .btn-download-proforma').addClass('d-none');
                                        } else {
                                            $('.invoiceBtn').addClass('d-none');
                                            $('.addPaymentBtn, .btn-download-proforma').removeClass('d-none');
                                        }
                                    }
                                }

                                function getInvoiceableAmountToPay() {
                                    return produceNumberFromInput($('#payments-total').attr('data-amount'))
                                }

                                const orderId = {{ $order->id }};
                                const paymentsData = {!! \Illuminate\Support\Js::from($order->payments->toArray()); !!};
                                const paymentMethods = {!! \Illuminate\Support\Js::from($paymentMethods); !!};
                            </script>
                            <script src="{{ asset('js/orders/payments.js') }}"></script>
                        @endpush


                    </div>
                </div>
            </div>
        @endif
    @endif

</div>

@push('js')
    <script src="{{ asset('js/orders/invoice_from_modal.js') }}"></script>
@endpush
