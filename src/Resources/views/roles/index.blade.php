<x-backend-layout>
    <x-slot name="header">
        <h2>{{ __('mfw::mfw-users.roles.title') }}</h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-outline-secondary mx-2" href="{{ route('mfw.role-groups.index') }}">
                <i class="fa-solid fa-layer-group"></i>
                {{ __('mfw::mfw-users.role_groups.nav') }}
            </a>
            <a class="btn btn-sm btn-success" href="{{ route('mfw.roles.create') }}">
                <i class="fa-solid fa-circle-plus"></i>
                {{ __('mfw::mfw-users.roles.create') }}
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
                    <th>{{ __('mfw::mfw-users.role_groups.key') }}</th>
                    <th>{{ __('mfw::mfw-users.roles.label') }}</th>
                    <th>{{ __('mfw::mfw-users.roles.group') }}</th>
                    <th></th>
                    <th width="220">{{ __('mfw::mfw-users.roles.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td><code>{{ $role->key }}</code></td>
                        <td>{{ $role->label }}</td>
                        <td>{{ $role->group?->label ?? '-' }}</td>
                        <td>
                            @if($role->is_system)
                                <span class="badge text-bg-secondary mfw-bg-red">{{ __('mfw::mfw-users.roles.system') }}</span>
                            @endif
                        </td>
                        <td>
                            <ul class="mfw-actions">
                                <li>
                                    <x-mfw::edit-link :route="route('mfw.roles.edit', $role->id)"/>
                                </li>
                                @if(!$role->is_system)
                                    <x-mfw::delete-modal-link
                                        reference="{{ $role->id }}"
                                        :title="__('mfw::mfw-users.roles.delete')"
                                    />
                                @endif
                            </ul>
                            @if(!$role->is_system)
                                <x-mfw::modal
                                    :route="route('mfw.roles.destroy', $role->id)"
                                    :question="__('mfw::mfw-users.roles.delete_confirm')"
                                    :title="__('mfw::mfw-users.roles.delete')"
                                    reference="destroy_{{ $role->id }}"
                                />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">{{ __('mfw::mfw-users.roles.empty') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-backend-layout>
