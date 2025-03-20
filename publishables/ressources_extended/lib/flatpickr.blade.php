@push('css')
    <link rel="stylesheet" href="{!! asset('vendor/flatpickr/flatpickr.min.css') !!}"/>
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
@endpush

@push('js')
    <script src="{!! asset('vendor/flatpickr/flatpickr.js') !!}"></script>
    <script src="{!! asset('vendor/flatpickr/locale/'. app()->getLocale().'.js') !!}"></script>
    <script>
        // data-config="enableTime=true,noCalendar=true,dateFormat=d/m/H H:i,minDate=today"

        function setDatepicker() {
            let datepickers = $('.datepicker');
            if (datepickers.length > 0) {
                datepickers.each(function () {
                    if (!$(this).hasClass('flatpickr-input')) {
                        const config = {};
                        config.dateFormat = 'd/m/Y';
                        config.time_24hr = true;
                        config.locale = "{!! app()->getLocale() !!}";

                        if ($(this).attr('id') === undefined) {
                            $(this).attr('id', 'dtpck-'+ (Math.random().toString(36).substring(7)));
                        }

                        let custom_config = $(this).attr('data-config');

                        if (custom_config != undefined && custom_config.length) {
                            custom_config = custom_config.split(',');
                            for (i = 0; i < custom_config.length; ++i) {
                                var values = custom_config[i].split('='),
                                    setValue;
                                switch (values[1]) {
                                    case 'true':
                                    case '1':
                                        setValue = true;
                                        break;
                                    case 'false':
                                    case '0':
                                        setValue = false;
                                        break;
                                    default:
                                        setValue = values[1];
                                }
                                config[values[0]] = setValue;
                            }
                        }
                        $(this).flatpickr(config);
                    }
                });
            }
        }

        $(function () {
            setDatepicker();
        });
    </script>
@endpush
