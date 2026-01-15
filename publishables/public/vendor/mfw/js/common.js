function sendMailFromModalResponse(result) {
    let myModal = $('#' + result.input.modal_id);
    removeVeil();
    myModal.on('hidden.bs.modal', function () {
        $(this).find('.messages').remove();
    });
    setTimeout(function () {
        myModal.find('.btn-close').trigger('click');
    }, 4000);
}

let token = function () {
        return $('meta[name="csrf-token"]').attr('content');
    },
    spinner = '<i class="core spinner fa fa-cog fa-spin fa-fw"></i>',
    setDelay = (function () {
        let timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })(),
    guid = (keyLength = 9) => Math.random().toString(36).slice(2, 2 + keyLength),
    produceNumberFromInput = function (input) {
        if (typeof input == 'string') {
            input = input.replace(/\s+/g, '');
        }
        let value = Number(input);
        return isNaN(value) ? 0 : value;
    };


setTimeout(function () {
    let tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    let tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

}, 1500);


// When modal is shown, manage topbar z-index
document.body.addEventListener('shown.bs.modal', function () {
    $('#topbar').removeClass('sticky-top');
});

// When modal is hidden
document.body.addEventListener('hidden.bs.modal', function () {
    $('#topbar').addClass('sticky-top');
});
