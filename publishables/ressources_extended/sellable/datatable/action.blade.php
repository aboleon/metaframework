<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('panel.sellables.edit', $data)"/>
    @if (!is_null($data->deleted_at))
        <x-mfw::restore-modal-link reference="{{ $data->id }}"/>
    @else
        <x-mfw::delete-modal-link reference="{{ $data->id }}" title="Archiver"/>
    @endif

</ul>

@if (!is_null($data->deleted_at))
    <x-mfw::modal :route="route('panel.sellables.restore', $data)"
                  question="Rétablir l'article <b>{{ $data->title }}</b> ?"
                  reference="restore_{{ $data->id }}"/>
@else
    <x-mfw::modal :route="route('panel.sellables.destroy', $data)"
                  question="Supprimer l'article <b>{{ $data->title }}</b> ?"
                  reference="destroy_{{ $data->id }}"/>

@endif
