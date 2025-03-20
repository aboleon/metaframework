@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.8.2/tinymce.min.js"></script>
    <script id="tinymce_settings" src="{!! asset('js/tinymce/default_settings.js') !!}"></script>
    <script>
        if ($('textarea.extended').length) {
            tinymce.init(default_tinymce_settings('textarea.extended'));
        }
        $(function() {
            if ($('textarea.simplified').length) {
                var url = "{!! asset('js/tinymce/simplified.js') !!}";
                $.when($.getScript(url)).then(function() {
                    tinymce.init(simplified_tinymce_settings('textarea.simplified'));
                });
            }
        });
    </script>
@endpush
