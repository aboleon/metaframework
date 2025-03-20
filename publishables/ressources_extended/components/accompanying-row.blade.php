<tr class="accompanying_row" data-identifier="{{ $identifier }}">
    <td class="room_selectables room_acoompanying">
        <x-mfw::select :values="[]" label="Chambre" name="order_accompanying.room_id."
                       :identifier="$model->id ? $identifier : ''"
                       :params="$model->id ? ['data-room-id' => $model->room_id] : []"/>
    </td>
    <td class="total_accompanying">
        <x-mfw::number name="order_accompanying.total."
                       min=1
                       label="Nombre accompagnants"
                       :value="$model->total"/>
    </td>
    <td class="accompanying">
        <x-mfw::textarea height="80"
                         name="order_accompanying.names."
                         label="Noms des accompagnants"
                         :value="$model->names"/>
    </td>
    <td>
        <x-mfw::simple-modal id="delete_accompanying_row"
                             class="btn btn-danger btn-sm mt-2"
                             title="Suppression d'une ligne des accompagnants"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeAccommopanyingRow"
                             :identifier="$identifier"
                             :modelid="$model->id"
                             text="Supprimer"/>
    </td>
</tr>
