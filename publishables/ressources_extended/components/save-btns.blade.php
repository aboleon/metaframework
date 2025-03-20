@props([
    'saveexit' => false
])

<button form="wagaia-form" class="btn btn-sm btn-warning">
    <i class="fa-solid fa-check"></i>
    Enregistrer
</button>

@if ($saveexit)
    <button form="wagaia-form" class="btn btn-sm btn-info mx-2" id="save-redirect-btn">
        <i class="fa-solid fa-check"></i>
        Enregistrer & Sortir
    </button>
    @push('js')
        <script>
            $('#save-redirect-btn').click(function (e) {
                e.preventDefault();
                $('#wagaia-form').append('<input type="hidden" name="save_and_redirect"/>');
                $('#wagaia-form').submit();
            });
        </script>
    @endpush
@endif
