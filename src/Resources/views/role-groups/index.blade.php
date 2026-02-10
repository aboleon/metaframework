<x-backend-layout>
    <x-slot name="header">
        <h2>{{ __('mfw-users.role_groups.title') }}</h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-outline-secondary mx-2" href="{{ route('mfw.roles.index') }}">
                <i class="fa-solid fa-shield"></i>
                {{ __('mfw-users.roles.nav') }}
            </a>
            <a class="btn btn-sm btn-success" href="{{ route('mfw.role-groups.create') }}">
                <i class="fa-solid fa-circle-plus"></i>
                {{ __('mfw-users.role_groups.create') }}
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
                    <th>{{ __('mfw-users.role_groups.key') }}</th>
                    <th>{{ __('mfw-users.role_groups.label') }}</th>
                    <th>{{ __('mfw-users.role_groups.description') }}</th>
                    <th>{{ __('mfw-users.role_groups.system') }}</th>
                    <th width="220">{{ __('mfw-users.role_groups.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td>{{ $group->key }}</td>
                        <td>{{ $group->label }}</td>
                        <td>{{ $group->description ?: '-' }}</td>
                        <td>
                            @if($group->is_system)
                                <span class="badge text-bg-secondary">{{ __('mfw.yes') }}</span>
                            @else
                                <span class="badge text-bg-light">{{ __('mfw.no') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('mfw.role-groups.edit', $group) }}" class="btn btn-sm btn-outline-primary">
                                {{ __('mfw-users.role_groups.edit') }}
                            </a>
                            @if(!$group->is_system)
                                <form method="post" action="{{ route('mfw.role-groups.destroy', $group) }}" class="d-inline" onsubmit="return confirm('{{ __('mfw-users.role_groups.delete_confirm') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('mfw-users.role_groups.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">{{ __('mfw-users.role_groups.empty') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-backend-layout>
