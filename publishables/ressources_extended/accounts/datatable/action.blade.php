<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('panel.accounts.edit', [
        'account' => $data,
        'role' => $role,
    ])"/>
    @if (!is_null($data->deleted_at))
        <x-mfw::restore-modal-link reference="{{ $data->id }}"/>
    @else
        <x-mfw::delete-modal-link reference="{{ $data->id }}" title="Archiver"/>
        <x-mfw::replicate-modal-link reference="{{ $data->id }}"/>
    @endif
</ul>
@if (!is_null($data->deleted_at))
    <x-mfw::modal :route="route('panel.accounts.restore', $data)"
                  question="Rétablir le compte {{ $data->first_name . ' '. $data->last_name }} ?"
                  reference="restore_{{ $data->id }}"/>
@else
    <x-mfw::modal :route="route('panel.accounts.destroy', $data)"
                  question="Supprimer le compte {{ $data->first_name . ' '. $data->last_name }} ?"
                  reference="destroy_{{ $data->id }}"/>
    <x-mfw::modal :route="route('panel.accounts.replicate', $data)"
                  question="Dupliquer le compte {{ $data->first_name . ' '. $data->last_name }} ?"
                  reference="replicate_{{ $data->id }}"
                  title="Duplication d'un contact"/>

@endif
