@php
    $isRoleLocked = isset($forcedRole) && is_array($forcedRole);
    $selected = collect(old('roles', $selectedRoleIds ?? []))->map(fn ($value) => (int) $value)->all();
    $canAssignDev = auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('dev');
@endphp

<div class="col-12 mb-3 mt-4">
    <div class="d-flex align-items-center mb-2">
        <b class="d-block">{{ __('mfw-users.users.roles') }}</b>
        <x-mfw::devmark/>
    </div>

    @if($isRoleLocked)
        <input type="hidden" name="roles[]" value="{{ $forcedRole['id'] }}"/>
        <span class="badge text-bg-secondary">{{ $forcedRole['label'] ?? '' }}</span>
    @else
        @forelse($roles as $roleKey => $role)
            @continue($roleKey === 'default')
            @continue($roleKey === 'dev' && !$canAssignDev)

            <div class="form-check me-3">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="roles_{{ $roleKey }}"
                    name="roles[]"
                    value="{{ $role['id'] }}"
                    @checked(in_array((int) $role['id'], $selected, true))
                />
                <label class="form-check-label" for="roles_{{ $roleKey }}">
                    {{ $role['label'] }}
                    @if(!empty($role['group_key']))
                        <small class="text-muted">({{ $role['group_key'] }})</small>
                    @endif
                </label>
            </div>
        @empty
            <div class="text-muted">{{ __('mfw-users.users.no_roles_available') }}</div>
        @endforelse
    @endif
</div>
