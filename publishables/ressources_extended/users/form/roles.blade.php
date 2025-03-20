@if (!isset($role) && request()->route()->getName() != 'panel.users.create_type')
    <div class="col-lg-12 mb-3 mt-4 {{ !auth()->user()->hasRole('dev') ? 'd-none' :'' }}">
        <div class="d-flex align-align-items-center mb-4">
            <b class="d-block">Rôles</b>
            <x-mfw::devmark/>
        </div>

        @if(!auth()->user()->hasRole('dev'))
            @php
                $roles = collect($roles)->filter(fn($item,$key) => $key != 'dev');
            @endphp
        @endif


        @foreach($roles as $role_key => $role)
            <div class="form-check me-3">
                <input class="form-check-input" type="checkbox"
                       id="roles_{{ $role_key }}" name="roles[]"
                       value="{{ $role['id'] }}"
                    {{ $account && $account->hasRole($role_key) ? 'checked':
    (!$account && $role == 'super-admin' ? 'checked' :'') }} />
                <label class="form-check-label" for="roles_{{ $role_key }}">
                    {{ trans('user_type.'.$role_key.'.label') }}
                </label>
            </div>
        @endforeach
    </div>
@else
    <input type="hidden" name="roles[]" value="{{ $role['id'] }}"/>
@endif
