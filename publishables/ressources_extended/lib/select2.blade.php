@pushonce('js')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/fr.min.js" integrity="sha512-2gJlMVg/vRkqj5vQZOrV+TYi/z/IZIOVbWBWXgcAMOH0BiDYpnmroPRTrUB5iT+IgfxU6OU0D43J2flnbg8lfA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .select2-selection {
            border-radius: 0 !important;
            padding: 11px 14px !important;
            font-size: 16px;
            border: var(--bs-border-width) solid var(--bs-border-color) !important;
        }
        .select2-search--dropdown .select2-search__field {

            padding: 8px 14px !important;
        }
        .select2-container .select2-selection--single {
            height: auto;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
        }
        .select2-results {
            color: black;
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable,
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #456777;
        }
    </style>
@endpushonce
