(function ($) {
    'use strict';

    $(function () {
        const resetAppContainer = $('body');
        const messages = $('#mfw-messages');
        const devMenu = $('.mfw-nav-dev-menu');
        const defaultAjaxUrl = devMenu.data('ajax');

        if (!messages.length || !defaultAjaxUrl) {
            return;
        }

        const executeDevAction = function (element) {
            const action = element.data('action');

            if (!action) {
                return;
            }

            const ajaxUrl = element.data('ajax') || defaultAjaxUrl;
            messages.attr('data-ajax', ajaxUrl);
            setVeil(resetAppContainer);
            mfwAjax('action=' + encodeURIComponent(action) + '&callback=removeVeil', messages);
            messages.attr('data-ajax', defaultAjaxUrl);
        };

        messages.attr('data-ajax', defaultAjaxUrl);

        $(document).on('click.mfwDevMenu', '.mfw-nav-dev-action', function (event) {
            event.preventDefault();
            executeDevAction($(this));
        });

        window.ajaxConfirmMfwDevMigrationFromModal = function () {
            $('#mfw-simple-modal .btn-confirm')
                .off('click.mfwDevMigrationConfirm')
                .on('click.mfwDevMigrationConfirm', function (event) {
                    const button = $(this);
                    let requestData = null;

                    if (button.hasClass('mfw-nav-dev-migrate-confirm')) {
                        requestData = 'action=artisanMigrate&callback=removeVeil&confirmed=1';
                    }

                    if (button.hasClass('mfw-nav-dev-migrate-rollback-confirm')) {
                        requestData = 'action=artisanMigrate&callback=removeVeil&rollback=1&confirmed=1';
                    }

                    if (!requestData) {
                        return;
                    }

                    event.preventDefault();
                    messages.attr('data-ajax', defaultAjaxUrl);
                    setVeil(resetAppContainer);
                    mfwAjax(requestData, messages);

                    const modal = document.getElementById('mfw-simple-modal');
                    const modalInstance = modal && typeof bootstrap !== 'undefined' && bootstrap.Modal
                        ? bootstrap.Modal.getInstance(modal)
                        : null;

                    modalInstance?.hide();
                });
        };

        window.ajaxRunMfwDevArtisanCommand = function () {
            const modal = $('#mfw-simple-modal');
            const form = modal.find('#mfw-nav-dev-artisan-command-form');
            const confirmButton = modal.find('.mfw-nav-dev-artisan-confirm');

            form.off('submit.mfwDevArtisan').on('submit.mfwDevArtisan', function (event) {
                event.preventDefault();
                confirmButton.trigger('click');
            });

            confirmButton.off('click.mfwDevArtisan').on('click.mfwDevArtisan', function (event) {
                event.preventDefault();

                const formElement = form.get(0);
                if (!formElement.reportValidity()) {
                    return;
                }

                const parameters = new URLSearchParams(new FormData(formElement));
                parameters.set('action', 'runArtisanCommand');
                parameters.set('callback', 'removeVeil');

                messages.attr('data-ajax', defaultAjaxUrl);
                setVeil(resetAppContainer);
                mfwAjax(parameters.toString(), messages);
                messages.attr('data-ajax', defaultAjaxUrl);
                modal.find('.btn-cancel').trigger('click');
            });
        };
    });
}(jQuery));
