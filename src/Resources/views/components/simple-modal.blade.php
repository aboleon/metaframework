<a href="#"
   data-model-id="{{ $modelid }}"
   data-identifier="{{ $identifier }}"
   class="{{ $class }}"
   data-bs-toggle="modal"
   data-bs-target="#mfw-simple-modal"
   data-modal-id="{{ $id }}"
   data-title="{!! $title !!}"
   data-body="{!! $body !!}"
   data-btn-confirm="{!! $confirm !!}"
   data-callback="{{ $callback }}"
   data-onshow="{{ $onshow }}"
   data-btn-confirm-class="{!! $confirmclass !!}"
   data-btn-cancel="{!! $cancel !!}"
   data-modalsize="{{ $modalsize }}"
>
    @if($linktitle)
        <span
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-title="{{ $linktitle }}"
        >
            @endif
            {!! $text !!}
            @if($linktitle)
        </span>
    @endif
</a>

@pushonce('js')
    <div class="modal fade" id="mfw-simple-modal" tabindex="-1" aria-labelledby="mfw-simple-modal_Label"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ __('mfw.close') }}"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal"></button>
                    <button type="button" class="btn btn-confirm"></button>
                </div>
            </div>
        </div>
    </div>
    <script>
        let mfwSimpleModal = new bootstrap.Modal(document.getElementById('mfw-simple-modal'));

        $(document).ready(function () {

            let jQuery_mfwSimpleModal = $('#mfw-simple-modal');

            jQuery_mfwSimpleModal.off().on('show.bs.modal', function (event) {
                let button = $(event.relatedTarget),
                    callback = button.data('callback'),
                    onshow = button.data('onshow');

                jQuery_mfwSimpleModal.find('.modal-dialog').addClass(button.data('modalsize')).end().find('.modal-title').html(button.data('title')).end().find('.modal-body').html(button.data('body')).end().find('.btn-cancel').html(button.data('btn-cancel')).end().find('.btn-confirm')
                    .addClass(button.data('btn-confirm-class'))
                    .addClass(button.data('modal-id'))
                    .attr('data-model-id', button.data('model-id'))
                    .attr('data-identifier', button.data('identifier'))
                    .html(button.data('btn-confirm'));

                if (callback !== undefined && typeof window[callback] === 'function') {
                    window[callback]();
                }
                if (onshow !== undefined && typeof window[onshow] === 'function') {
                    window[onshow](button.data('identifier'));
                }

            }).on('hide.bs.modal', function () {
                jQuery_mfwSimpleModal.find('.modal-title, .modal-body, .btn-confirm, .btn-cancel').html('').end().find('.btn-confirm').attr('class', 'btn btn-confirm').removeAttr('data-model-id').removeAttr('data-identifier');
                jQuery_mfwSimpleModal.find('button, a, input, select, textarea, [tabindex]').blur();
            });
        });
    </script>

@endpushonce
