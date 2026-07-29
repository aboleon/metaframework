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
   data-show-header="{{ $title ? 1 : 0 }}"
   data-show-footer="{{ $footer ? 1 : 0 }}"
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
                            aria-label="{{ __('mfw::mfw.close') }}"></button>
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
            let toBoolean = function (value) {
                return value === true || value === 1 || value === '1';
            };

            jQuery_mfwSimpleModal.off().on('show.bs.modal', function (event) {
                let button = $(event.relatedTarget || []),
                    callback = button.data('callback'),
                    onshow = button.data('onshow'),
                    showHeader = toBoolean(button.data('show-header')),
                    showFooter = toBoolean(button.data('show-footer')),
                    modalDialog = jQuery_mfwSimpleModal.find('.modal-dialog'),
                    modalHeader = jQuery_mfwSimpleModal.find('.modal-header'),
                    modalTitle = jQuery_mfwSimpleModal.find('.modal-title'),
                    modalBody = jQuery_mfwSimpleModal.find('.modal-body'),
                    modalFooter = jQuery_mfwSimpleModal.find('.modal-footer'),
                    cancelButton = jQuery_mfwSimpleModal.find('.btn-cancel'),
                    confirmButton = jQuery_mfwSimpleModal.find('.btn-confirm');

                if (!button.length) {
                    return;
                }

                modalDialog
                    .removeClass('modal-sm modal-lg modal-xl')
                    .addClass(button.data('modalsize'));

                modalHeader.toggleClass('d-none', !showHeader);
                modalFooter.toggleClass('d-none', !showFooter);

                modalTitle.html(showHeader ? (button.data('title') || '') : '');
                modalBody.html(button.data('body') || '');

                if (showFooter) {
                    cancelButton.html(button.data('btn-cancel') || '');
                    confirmButton
                        .addClass(button.data('btn-confirm-class'))
                        .addClass(button.data('modal-id'))
                        .attr('data-model-id', button.data('model-id'))
                        .attr('data-identifier', button.data('identifier'))
                        .html(button.data('btn-confirm') || '');
                } else {
                    cancelButton.html('');
                    confirmButton
                        .attr('class', 'btn btn-confirm')
                        .removeAttr('data-model-id')
                        .removeAttr('data-identifier')
                        .html('');
                }

                if (callback !== undefined && typeof window[callback] === 'function') {
                    window[callback]();
                }
                if (onshow !== undefined && typeof window[onshow] === 'function') {
                    window[onshow](button.data('identifier'));
                }

            }).on('hide.bs.modal', function () {
                jQuery_mfwSimpleModal.find('.modal-dialog').removeClass('modal-sm modal-lg modal-xl');
                jQuery_mfwSimpleModal.find('.modal-header, .modal-footer').removeClass('d-none');
                jQuery_mfwSimpleModal.find('.modal-title, .modal-body, .btn-confirm, .btn-cancel').html('').end().find('.btn-confirm').attr('class', 'btn btn-confirm').removeAttr('data-model-id').removeAttr('data-identifier');
                jQuery_mfwSimpleModal.find('button, a, input, select, textarea, [tabindex]').blur();
            });
        });
    </script>

@endpushonce
