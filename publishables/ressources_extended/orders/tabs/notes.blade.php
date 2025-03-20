<div class="tab-pane fade"
     id="notes-tabpane"
     role="tabpanel"
     aria-labelledby="notes-tabpane-tab">

    <h4>Notes</h4>
    <div id="note-messages" data-ajax="{{ route('ajax') }}"></div>
    <div class="row m-0">
        <div class="col-md-12 mb-4 ps-0">
            <div id="notes">
                @if($order->notes->isNotEmpty())
                    @foreach($order->notes->sortByDesc('id') as $note)
                        <x-order-note :note="$note" :order="$order"/>
                    @endforeach
                @endif
            </div>

            <div id="notes-banner" class="mb-3 {{  $order->notes->isNotEmpty() ? 'd-none' : '' }}">
                <x-mfw::notice message="Aucune note saisie."/>
            </div>
            <button type="button" id="add-note" class="btn btn-sm btn-danger">Ajouter une note</button>
        </div>
    </div>
</div>
<template id="note" data-order-id="{{ $order->id }}">
    <x-order-note :note="new \App\Models\Order\Note()" :order="$order"/>
</template>

@push('callbacks')
    <script>
        function ajaxDeleteNoteFromModal() {
            $('.delete_note').off().on('click', function () {
                clearNoteMessages();
                let notes = $('.order-note'),
                    target = $(this).attr('data-identifier'),
                    note = notes.filter(function () {
                        return $(this).attr('data-random') == target;
                    }).first();
                $('#mfw-simple-modal').find('.btn-cancel').trigger('click');
                let note_id = produceNumberFromInput(note.attr('data-id'));
                note_id > 0
                    ? ajax('action=removeOrderNote&callback=removeOrderNote&id=' + note_id, $('#note-messages'))
                    : note.remove();
                clearNotesBanner();
            });
        }

        function clearNotesBanner() {
            if ($('#notes > div').length < 1) {
                $('#notes-banner').removeClass('d-none');
            }
        }

        function clearNoteMessages() {
            $('#note-messages').html('');
        }

        function dispatchAddedNote(result) {
            if (!result.hasOwnProperty('errors')) {
                let note = $('#notes').find('[data-random=' + result.input.random + ']');
                note.find('label').text(result.title);
                note.attr('data-id', result.note_id);
                note.find('textarea').prop('disabled', true);
                note.find('.save').addClass('d-none');
                note.find('.controls').removeClass('justify-content-between').addClass('justify-content-end');
                note.prependTo($('#notes'));
            }
        }

        function removeOrderNote(result) {
            if (!result.hasOwnProperty('errors')) {
                $('#notes').find('[data-id=' + result.input.id + ']').remove();
                clearNotesBanner();
            }
        }
    </script>
@endpush

@push('js')

    <script>
        const notes = {
            c: function () {
                return $('#notes');
            },
            orderId: function () {
                return $('template#note').data('order-id');
            },
            add: function () {
                $('#add-note').off().on('click', function () {
                    clearNoteMessages();
                    notes.c().append($($('template#note')).html());
                    let random = guid(),
                        last_note = notes.c().find('.order-note').last();
                    last_note.attr('data-random', random);
                    last_note.find('.delete').attr('data-identifier', random);
                    notes.save();
                    $('#notes-banner').addClass('d-none');
                    ajaxDeleteNoteFromModal();
                });
            },
            save: function () {
                $('.order-note button.save').off().on('click', function () {
                    let note = $(this).closest('.order-note'),
                        note_id = Number(note.attr('data-id'));
                    ajax('action=addOrderNote&note=' + (note.find('textarea').val()) + '&callback=dispatchAddedNote&order_id=' + notes.orderId() + '&random=' + note.attr('data-random') + '&id=' + note_id, $('#note-messages'));
                });
            },
            dispatchRandomIds: function () {
                let random = guid();
                this.c().find('> div').attr('data-random', random);
                this.c().find('> div a.delete').attr('data-identifier', random);
            },
            init: function () {
                setTimeout(function () {
                    notes.dispatchRandomIds();
                    ajaxDeleteNoteFromModal();
                }, 500);
                this.add();
                this.save();
            },
        };
        notes.init();
    </script>

@endpush

