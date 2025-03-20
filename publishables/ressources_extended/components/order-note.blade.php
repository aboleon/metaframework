<div class="order-note pb-4 mb-3 mfw-line-separator" data-id="{{ $note->id }}">
    <x-mfw::textarea name="order_note[{{ $note->id }}]" :value="$note->note"
                     label="Note {{ $note->id ? 'créée le '. $note->created_at->format('d/m/Y à H:i'). ' par '. $note->createdBy->names() : '' }}"
                     :params="$note->id ? ['disabled'=>'disabled'] : []"/>
    <div class="controls mt-3 d-flex justify-content-{{ $note->id ? 'end' : 'between' }}">
        @if ($order->id)
            <button type="button" class="save btn btn-sm btn-success{{ $note->id ? ' d-none' : '' }}">Enregistrer
            </button>
        @endif
        <x-mfw::simple-modal id="delete_note"
                             class="delete btn btn-sm btn-secondary"
                             title="Suppression d'une note"
                             confirmclass="btn-danger"
                             confirm="Supprimer"
                             callback="ajaxDeleteNoteFromModal"
                             text='Supprimer'/>
    </div>
</div>
