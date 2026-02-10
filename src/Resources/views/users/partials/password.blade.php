@php
    $isEdit = (bool) ($account->id ?? null);
@endphp

<div class="col-12">
    <h4>{{ __('mfw-users.users.password') }}</h4>

    @if($isEdit)
        <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" name="password_change" id="password_change"/>
            <label class="form-label" for="password_change">{{ __('mfw-users.users.password_change') }}</label>
        </div>
    @else
        <input type="hidden" name="password_change" value="1"/>
    @endif

    <div class="form-check mb-2">
        <input type="checkbox" class="form-check-input" id="random_password" name="random_password" @checked(!$isEdit)>
        <label class="form-label" for="random_password">{{ __('mfw-users.users.password_random') }}</label>
    </div>
</div>

<div class="col-12 col-xl-6 mt-3">
    <div class="form-group">
        <label class="form-label" for="password">{{ __('mfw-users.users.password_new') }}</label>
        <input id="password" type="password" name="password" class="form-control" value="" @if($isEdit) disabled @endif/>
        <span>{{ __('mfw-users.users.password_min_chars', ['min' => 8]) }}</span>
    </div>
</div>
<div class="col-12 col-xl-6 mt-3">
    <div class="form-group">
        <label class="form-label" for="password_confirmation">{{ __('mfw-users.users.password_repeat') }}</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" @if($isEdit) disabled @endif/>
    </div>
</div>

@push('js')
    <script>
        $(function () {
            let randomPassword = $('#random_password');
            let passwordChange = $('#password_change');
            let passwordFields = $('#password, #password_confirmation');
            let isEdit = {{ ($account->id ?? null) ? 'true' : 'false' }};

            let syncPasswordFields = function () {
                if (randomPassword.is(':checked')) {
                    passwordFields.prop('disabled', true).val('');
                    return;
                }

                if (!isEdit || passwordChange.is(':checked')) {
                    passwordFields.prop('disabled', false);
                    return;
                }

                passwordFields.prop('disabled', true).val('');
            };

            randomPassword.on('change', syncPasswordFields);
            passwordChange.on('change', syncPasswordFields);
            syncPasswordFields();
        });
    </script>
@endpush
