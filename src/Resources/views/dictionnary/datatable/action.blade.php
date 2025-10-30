<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('mfw.dictionnary.edit', $item)" />
    @role('dev')
    <x-mfw::delete-modal-link reference="{{ $item->id }}" />
    <x-mfw::devmark />
    @endrole
</ul>
<x-mfw::modal :route="route('mfw.dictionnary.destroy', $item->id)"
              question="Supprimer {{ $item->name }} ?"
              reference="destroy_{{ $item->id }}" />
