<tr class="refund_row" data-identifier="{{ $identifier }}">

    <td class="room_selectables align-top">
        {{  $model->date }}
    </td>
    <td style="width: 20%;">
        {{ $model->object }}
    </td>
    <td>
        {{ $model->amount }}
    </td>
    <td>
        {{  \MetaFramework\Accessors\VatAccessor::rate($model->vat_id) }}%
    </td>
    <td>
        {{ \MetaFramework\Accessors\VatAccessor::netPriceFromVatPrice($model->amount, $model->vat_id) }}
    </td>
    <td>
        {{ \MetaFramework\Accessors\VatAccessor::vatForPrice($model->amount, $model->vat_id) }}
    </td>

    @if ($iteration < 2)
        <td class="align-middle text-center" rowspan="{{ $total }}">
            <a href="{{ route('panel.manager.event.refunds.edit', ['event' => $event, 'refund' => $model->refund_id, ] ) }}" class="mfw-edit-link btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top"
               data-bs-title="{{ __('mfw.edit') }}">
                <i class="fas fa-pen"></i></a>
            <a href="{{ route('pdf-printer', ['type' => 'refundable', 'identifier' => $uuid]) }}" class="mfw-edit-link btn btn-sm btn-danger" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Avoir">
                <i class="fas fa-file-pdf"></i></a>

        </td>
    @endif
</tr>
