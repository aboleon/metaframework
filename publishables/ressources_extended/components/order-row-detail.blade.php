@php use App\Enum\OrderClientType; @endphp

<thead>
{{-- style="background-color: #d1e7dd;" --}}
<tr>
    <th colspan="11" class="fs-6 bg-secondary-subtle">
        Commande #{{ $order->id }}
    </th>
</tr>
<tr>
    <th>Date</th>
    <th>Contenu</th>
    <th class="text-end">Prix TTC</th>
    <th class="text-end">Prix Payé</th>
    <th>État</th>
    <th>Affectation</th>
    <th>Payeur</th>
    <th>Origine</th>
    <th>Num Fact</th>
    <th>Annulation</th>
    <th class="text-end">Actions</th>
</tr>
</thead>
<tbody class="order-{{ $order->id }}">
<tr>
    <td>{{ $order->created_at->format('d/m/Y') }}</td>
    <td>
        @if($serviceCart->isNotEmpty())
            <b>Prestations :</b><br>
            @forelse($serviceCart as $shoppable)

                {{ $services->where('id', $shoppable->service_id)->first()?->title ?? 'NC' }}
                x {{ $shoppable->quantity }}
                @if ($shoppable->total_pec)
                    <x-pec-mark />
                @endif
                <div class="text-danger">
                    @if ($shoppable->cancellation_request)
                        <small>Demande d'annulation faite
                            le {{ $shoppable->cancellation_request->format('d/m/Y à H:i') }}</small>
                    @endif
                    @if($shoppable->cancelled_at)

                        <small> | Annulé le {{$shoppable->cancelled_at?->format("d/m/Y à H\hi")}}</small>
                    @endif
                </div>
                <br>
            @empty
            @endforelse
        @endif
        @if ($hotels)
            <b>Hébergement :</b><br>
            {!! implode('<br>', $hotels) !!}
            @if ($order->amended_order_id)
                <br>
                <a href="{{ route('panel.manager.event.orders.edit', ['event' => $order->event_id, 'order' => $order->amended_order_id]) }}"
                   class="text-danger"><small>{{ __('front/order.amended_order', ['order' => $order->amended_order_id]) }}
                </a>
            @endif
            @if ($order->amended_by_order_id )
                <br>
                <a href="{{ route('panel.manager.event.orders.edit', ['event' => $order->event_id, 'order' => $order->amended_by_order_id ]) }}"
                   class="text-danger">{{ __('front/order.was_amended', ['order' => $order->amended_by_order_id ]) }}</a>
            @endif

        @endif
    </td>
    <td class="text-end">{{ $price() }}</td>
    <td class="text-end">{{ $paid() }}</td>
    <td>
        <span class="{{ $orderAccessor->isOrator() || $orderAccessor->isPaid() ? 'mfw-status bg-success': '' }}">
            {{ $orderAccessor->isOrator() ? 'Offert' : \App\Enum\OrderStatus::translated($order->status) }}
        </span>
    </td>
    <td class="text-nowrap">
        @if ($orderAccessor->isRegular())
            {{ $order->account->names() }}
        @else
            @if($orderAccessor->isFrontGroupOrder())
                {{ $order->suborders->pluck('account')->map(fn($item) => $item->names())->join('<br>') }}
            @else
                {{-- //TODO Faire les attributions groupe BO --}}
            @endif
        @endif
    </td>
    <td>
        {{ $order->invoiceable?->account?->names() }}
        <small class="d-block fw-bold">
            {{ OrderClientType::translated($orderAccessor->invoiceable()?->account_type??'') }}
        </small>
    </td>
    <td><span
            class="{{ !$orderAccessor->isMadeByAdmin() ? 'mfw-status offline': '' }}">{{ $order->origin }}</span>
    </td>
    <td>{{ $order->invoice()?->invoice_number }}</td>
    <td>
        {!! $hasPartialCancellation !!}
        @if(
            $eventContact?->order_cancellation &&
            $orderAccessor->isRegular() &&
            $eventContact?->user_id === $order->client_id
        )
            <x-back.order-cancellation-pill/>
        @endif
    </td>
    <td>
        <ul class="mfw-actions">
            <x-mfw::edit-link
                :route="route('panel.manager.event.orders.edit', ['event' => $order->event_id, 'order' => $order->id])"/>

            @if ($order->invoices->isNotEmpty())
                @foreach($order->invoices->sortBy('proforma') as $invoice)
                    <li>
                        <a href="{{ route('pdf-printer', ['type' => 'invoice', 'identifier' => $order->uuid]) . ($invoice->proforma ? '?proforma='.$invoice->id : '')  }}"
                           class="mfw-edit-link btn btn-sm btn-{{ $invoice->proforma ? 'warning' : 'success' }}"
                           target="_blank"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top" data-bs-title="Facture {{ $invoice->proforma ? 'Proforma' : '' }}">
                            <i class="fa-solid fa-f"></i>
                            @if ($invoice->proforma)
                                <i class="fa-solid fa-p"></i>
                            @endif
                        </a>
                    </li>
                @endforeach
            @endif

            @if ($order->client_type == \App\Enum\OrderClientType::ORATOR->value)
                <li>
                    <a href="{{ route('pdf-printer', ['type' => 'invoice', 'identifier' => $order->uuid]) . '?proforma=proforma'  }}"
                       class="mfw-edit-link btn btn-sm btn-warning" target="_blank"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top" data-bs-title="Facture Proforma">
                        <i class="fa-solid fa-f"></i>
                        <i class="fa-solid fa-p"></i>
                    </a>
                </li>
            @endif
        </ul>

    </td>
</tr>
@if ($order->refunds->isNotEmpty())
    <tr>
        <th colspan="11" style="background-color: #f8d7da;">
            Avoirs
        </th>
    </tr>
    @foreach($order->refunds as $refund)
        <tr class="refund-{{ $refund->id }}">
            <td>
                @if ($refund->items->count() > 1)
                    <b>Dates</b>
                    <hr/>
                @endif
                @foreach($refund->items as $record)
                    {{ $record->date }}<br>
                @endforeach
            </td>
            <td>
                @if ($refund->items->count() > 1)
                    <b>Montant global</b>
                    <hr/>
                @endif
                @foreach($refund->items as $record)
                    {{ $record->object }}<br>
                @endforeach
            </td>
            <td class="text-end">
                @if ($refund->items->count() > 1)
                    <b>{{ \MetaFramework\Accessors\Prices::readableFormat($refund->items->sum('amount')) }}</b>
                    <hr/>
                @endif
                @foreach($refund->items as $record)
                    {{ \MetaFramework\Accessors\Prices::readableFormat($record->amount) }}<br>
                @endforeach
            </td>
            <td colspan="8" class="text-end">
                <a href="{{ route('pdf-printer', ['type' => 'refundable', 'identifier' => $refund->uuid]) }}"
                   class="mfw-edit-link btn btn-sm btn-danger" target="_blank" data-bs-toggle="tooltip"
                   data-bs-placement="top" data-bs-title="Avoir">
                    <i class="fa-solid fa-a"></i>
                </a>
            </td>
        </tr>
    @endforeach
@endif
</tbody>
