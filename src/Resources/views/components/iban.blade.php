<div class="{{  $class }}">
    <x-mfw-input::input :name="$name" class="iban" :value="$value" :label="$label"/>
    <div class="feedback invalid-feedback d-none">IBAN invalide</div>
    <div class="feedback valid-feedback d-none">IBAN valide</div>
</div>
@pushonce('js')
    <script src="{{ asset('vendor/mfw/components/iban-validator.js') }}"></script>
    <script>
        $(function () {
            $('.iban').on('blur', function () {
                let c = $(this).closest('.iban-validator'), input = $(this);
                $(this).removeClass('is-valid is-invalid');
                c.find('.feedback').addClass('d-none');
                c.find('.invalid-feedback.d-block').remove();
                setDelay(function () {
                    if (input.val().length) {
                        console.log(isValidIBANNumber(input.val()), 'valid iban');
                        let validatedIban = isValidIBANNumber(input.val());

                        if (validatedIban.is_valid) {
                            c.find('.valid-feedback').removeClass('d-none');
                            input.addClass('is-valid');
                            input.val(validatedIban.iban_validated);
                        } else {
                            c.find('.invalid-feedback').removeClass('d-none');
                            input.addClass('is-invalid');
                        }
                    }
                }, 500);
            });
        });
    </script>
@endpushonce
