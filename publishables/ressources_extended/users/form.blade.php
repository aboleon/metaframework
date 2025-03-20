<div class="row tabbable">
    <div class="col-sm-6 bloc-editable">

            <h2>Informations de base</h2>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>Prénom</strong>
                        <input class="form-control" type="text" name="first_name" value="{{ $account->first_name ?? old('first_name') }}" placeholder="Prénom *" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>Nom</strong>
                        <input class="form-control" type="text" name="last_name" value="{{ $account->last_name ?? old('last_name') }}" placeholder="Nom *" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>e-mail</strong>
                        <input class="form-control" type="email" name="email" value="{{ $account->email ?? old('email') }}" placeholder="e-mail *" required>
                    </div>
                </div>
            </div>

            <br>

            <div class="row">

                <div class="col-sm-12">
                    <input style="display: inline-block;" type="checkbox" name="password_change" id="password_change" /> <strong style="display: inline-block;">Changer le mot de passe</strong>
                    <br>
                    <input type="checkbox" id="random_password" name="random_password"><label for="random_password">&nbsp; Générer un mot de passe aléatoire</label>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>Nouveau mot de passe</strong>
                        <input type="password" name="password" class="form-control" value="" disabled />
                        <span>Au minimum 8 caractères</span>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>Répeter le mot de passe</strong>
                        <input type="password" name="password_confirmation" class="form-control" disabled />
                    </div>
                </div>
            </div>

    </div>
</div>

@push('js')
<script>
    $(function() {
        $('#password_change').click(function() {
            if ($(this).is(':checked')) {
                $(':password').removeAttr('disabled');
            } else {
                $(':password').prop('disabled',true);
            }
        });
    });
</script>
@endpush
