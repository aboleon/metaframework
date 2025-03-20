<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('panel.mailtemplates.edit', $data)"/>
    <x-mfw::delete-modal-link reference="{{ $data->id }}" title="Archiver"/>
</ul>

<x-mfw::modal :route="route('panel.mailtemplates.destroy', $data)"
              question="Supprimer l'email <b>{{ $data->title }}</b> ?"
              reference="destroy_{{ $data->id }}"/>
