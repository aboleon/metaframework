<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('panel.users.edit', $data->id)"/>
    @if (!is_null($data->deleted_at))
        <x-mfw::restore-modal-link reference="{{ $data->id }}"/>
    @else
        <x-mfw::delete-modal-link reference="{{ $data->id }}" title="Archiver"/>
    @endif
</ul>
@if (!is_null($data->deleted_at))
    <x-mfw::modal :route="route('panel.users.restore', $data)"
                  question="Rétablir le compte {{ $data->name }} ?"
                  reference="restore_{{ $data->id }}"/>
@else
    <x-mfw::modal :route="route('panel.users.destroy', $data)"
                  question="Supprimer le compte {{ $data->name }} ?"
                  reference="destroy_{{ $data->id }}"/>

@endif
