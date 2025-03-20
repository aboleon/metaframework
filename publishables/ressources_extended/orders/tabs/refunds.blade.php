<div class="tab-pane fade"
     id="refunds-tabpane"
     role="tabpanel"
     aria-labelledby="refunds-tabpane-tab">
    <div class="container" id="invoice-cancel-container">
        <div class="row g-5">
            <div class="col">
                @if ($orderAccessor->isOrder() && !$order->external_invoice)

                    @if($order->refunds->isNotEmpty())
                        @foreach($order->refunds->load('items') as $refund)
                            <table class="table">
                                @php
                                    $refunds_count = $refund->items->count();
                                @endphp
                                <caption>Avoir #{{ $refund->refund_number }}
                                    du {{ $refund->created_at->format('d/m/Y') }}</caption>
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Désignation</th>
                                    <th>Montant TTC</th>
                                    <th>Taux TVA</th>
                                    <th>Montant HT</th>
                                    <th>Montant TVA</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($refund->items as $item)
                                    <x-refund-print-row :event="$event"
                                                        :model="$item"
                                                        :iteration="$loop->iteration"
                                                        :total="$refunds_count"
                                                        :uuid="$refund->uuid"/>
                                @endforeach
                                </tbody>
                            </table>
                        @endforeach
                    @else
                        <h4>Avoirs</h4>
                        <x-mfw::notice message="Aucun avoir pour cette commande" class="mb-3"/>
                    @endif


                    <div class="mfw-line-separator mb-4"></div>

                    <a href="{{ route('panel.manager.event.orders.refunds.create', ['event' => $event, 'order' => $order] ) }}"
                       class="btn btn-sm btn-default" id="add-refund">Créer un avoir</a>

                @else
                    Facturation externe
                @endif
            </div>
        </div>
    </div>
</div>
