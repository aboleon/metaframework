<button form="mfw-form" class="btn btn-sm btn-warning mx-2">
    <i class="fa-solid fa-check"></i>
    {{ __('mfw::mfw.save') }}
</button>

<button form="mfw-form" class="btn btn-sm btn-info mx-2" id="mfw-save-redirect-btn">
    <i class="fa-solid fa-check"></i>
    {{ __('mfw::mfw.save_quit') }}
</button>
@push('js')
    <script>
        $('#mfw-save-redirect-btn').click(function(e) {
            e.preventDefault();
           $('#mfw-form').append('<input type="hidden" name="save_and_redirect"/>');
           $('#mfw-form').submit();
        });
    </script>
@endpush
