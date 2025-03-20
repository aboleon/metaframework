<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('panel.participationtypes.edit', $data)"/>
    @role('dev')
    <x-mfw::delete-modal-link reference="{{ $data->id }}"/>
    @endrole
</ul>
<x-mfw::modal :route="route('panel.participationtypes.destroy', $data->id)"
              question="Supprimer {{ $data->name }} ?"
              reference="destroy_{{ $data->id }}"/>
