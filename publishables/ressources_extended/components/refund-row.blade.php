<tr class="refund_row" data-identifier="{{ $identifier }}">

    <td class="room_selectables align-top">
        <x-mfw::datepicker name="order_refund.date." :value="$model->date" :randomize="true"/>
    </td>
    <td>
        <x-mfw::textarea height="100" name="order_refund.object." :value="$model->object" :randomize="true"/>
    </td>
    <td class="align-top">
        <x-mfw::number name="order_refund.amount." class="amount" :value="$model->amount" :randomize="true"/>
    </td>
    <td class="align-top">
        <x-mfw::select
            :values="\MetaFramework\Accessors\VatAccessor::selectableOptionHtmlList(affected:$model->vat_id ?: \MetaFramework\Accessors\VatAccessor::defaultRate()->id)"
            :randomize="true"
            name="order_refund.vat_id."
            :nullable="false"/>
    </td>
    <td class="align-top">
        @if ($model->id)
            <a href="#" class="mfw-edit-link btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top"
               data-bs-title="{{ __('mfw.edit') }}">
                <i class="fas fa-pen"></i></a>
        @endif

        <x-mfw::simple-modal id="delete_refund_row"
                             class="btn btn-danger btn-sm"
                             title="Suppression d'une ligne d'avoir"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeRefundRow"
                             :identifier="$identifier"
                             :modelid="$model->id"
                             text="Supprimer"/>

    </td>
</tr>
