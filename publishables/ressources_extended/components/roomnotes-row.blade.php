<tr class="roomnotes_row" data-identifier="{{ $identifier }}">
    <td class="room_selectables align-top">
        <x-mfw::select :values="[]" label="Chambre"
                       name="order_roomnotes.room_id."
                       :affected="$model?->room_id??0"
                       :identifier="$model->id ? $identifier : ''"
                       :params="$model->id ? ['data-room-id' => $model->room_id] : []"/>
    </td>
    <td>
        <x-mfw::textarea name="order_roomnotes.note." label="Commentaire" value="{{ $model->note }}" />
    </td>
    <td>
        <x-mfw::simple-modal id="delete_roomnotes_row"
                             class="btn btn-danger btn-sm mt-2"
                             title="Suppression d'un commentaire sur chambre"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="removeRoomNotesRow"
                             :identifier="$identifier"
                             :modelid="$model->id"
                             text="Supprimer"/>
    </td>
</tr>
