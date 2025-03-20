@php
    $error = $errors->any();
@endphp
<div class="col-12">
    <h4>Mot de passe</h4>
    <div class="form-check {{ !$account->id ? 'd-none' : '' }}">
        <input type="checkbox" class="form-check-input" name="password_change" id="password_change"/>
        <label class="form-label" for="password_change">Changer le mot de passe</label>
    </div>
    <div class="form-check ">
        <input type="checkbox" class="form-check-input" id="random_password" name="random_password" {{ !$account->id ? 'checked' :'' }}>
        <label class="form-label" for="random_password">Générer un mot de passe aléatoire</label>
    </div>
    <div id="send_password_by_mail">
        <x-mfw::checkbox :params="$account->id ? ['disabled'=> true] : []" name="send_password_by_mail" label="Envoyer le mot de passe par e-mail" value="send_password_by_mail" :affected="$error ? old('send_password_by_mail') : null"/>
    </div>
</div>
<div class="col-12 col-xl-6 mt-3">
    <div class="form-group" style="clear: both;">
        <label class="form-label" for="password">Nouveau mot de passe</label>
        <input id="password" type="password" name="password" class="form-control" value="" {{ $account->id ? 'disabled' : '' }} />
        <span>Au minimum 8 caractères</span>
    </div>
</div>
<div class="col-12 col-xl-6 mt-3">
    <div class="form-group">
        <label class="form-label" for="password_confirmation">Répeter le mot de passe</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" {{ $account->id ? 'disabled' : '' }}/>
    </div>
</div>
<x-mfw::validation-error field="password"/>

@push('js')
    <script>
        $(function () {

            let random_password = $('#random_password'),
                send_by_email = $('#send_password_by_mail :checkbox'),
                password_change = $('#password_change');

            if (random_password.is(':checked')) {
                $(':password').prop('disabled', true);
            }

            password_change.click(function () {
                if ($(this).is(':checked') && !$('#random_password').is(':checked')) {
                    $(':password').removeAttr('disabled');
                } else {
                    $(':password').prop('disabled', true);
                }
            });
            random_password.click(function () {
                if ($(this).is(':checked')) {
                    $(':password').prop('disabled', true);
                    $('#password_change').prop('checked', true);

                } else {
                    $(':password').removeAttr('disabled');
                }
            });
            password_change.add(random_password).click(function () {
                if (password_change.is(':checked') || random_password.is(':checked')) {
                    send_by_email.prop('disabled', false);
                } else {
                    send_by_email.prop('disabled', true);
                }
            });
        });
    </script>
@endpush
