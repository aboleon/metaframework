<x-backend-layout>
    <x-slot name="header">
        <h2>{{ __('mfw-users.roles.title') }}</h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-outline-secondary mx-2" href="{{ route('mfw.role-groups.index') }}">
                <i class="fa-solid fa-layer-group"></i>
                {{ __('mfw-users.role_groups.nav') }}
            </a>
            <a class="btn btn-sm btn-success" href="{{ route('mfw.roles.create') }}">
                <i class="fa-solid fa-circle-plus"></i>
                {{ __('mfw-users.roles.create') }}
            </a>
        </div>
    </x-slot>

    <x-mfw-support::validation-errors/>
    <x-mfw-support::response-messages/>

    <div class="shadow p-4 bg-body-tertiary rounded">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>{{ __('mfw-users.roles.key') }}</th>
                    <th>{{ __('mfw-users.roles.label') }}</th>
                    <th>{{ __('mfw-users.roles.group') }}</th>
                    <th>{{ __('mfw-users.roles.system') }}</th>
                    <th width="220">{{ __('mfw-users.roles.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>{{ $role->key }}</td>
                        <td>{{ $role->label }}</td>
                        <td>{{ $role->group?->label ?? '-' }}</td>
                        <td>
                            @if($role->is_system)
                                <span class="badge text-bg-secondary">{{ __('mfw.yes') }}</span>
                            @else
                                <span class="badge text-bg-light">{{ __('mfw.no') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('mfw.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('mfw-users.roles.edit') }}
                            </a>
                            @if(!$role->is_system)
                                <form method="post" action="{{ route('mfw.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('{{ __('mfw-users.roles.delete_confirm') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('mfw-users.roles.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">{{ __('mfw-users.roles.empty') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-backend-layout>
