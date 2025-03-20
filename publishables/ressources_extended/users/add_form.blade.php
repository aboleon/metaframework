<x-mfw::validation-errors/>

<form method="post" action="{{ $route }}">
    @if (isset($method))
        @method($method)
    @endif

    <div class="row tabbable">
        <div class="col-sm-6 bloc-editable">
            <h2>Informations de base</h2>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>Prénom</strong>
                        <input class="form-control" type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Prénom *" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>Nom</strong>
                        <input class="form-control" type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Nom *" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <strong>e-mail</strong>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}" placeholder="e-mail *" required>
                    </div>
                </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <strong>Numéro de téléphone</strong>
                            <input class="form-control" type="text" name="phone" value="{{ old('phone') }}" placeholder="Numéro de téléphone *">
                        </div>
                    </div>
            </div>
        </div>
    </div>
</form>
