<x-backend-layout>
    <x-slot name="header">
        <h2>{{ __('mfw::mfw-users.role_groups.title') }}</h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-outline-secondary mx-2" href="{{ route('mfw.roles.index') }}">
                <i class="fa-solid fa-shield"></i>
                {{ __('mfw::mfw-users.roles.nav') }}
            </a>
            <a class="btn btn-sm btn-success" href="{{ route('mfw.role-groups.create') }}">
                <i class="fa-solid fa-circle-plus"></i>
                {{ __('mfw::mfw-users.role_groups.create') }}
            </a>
        </div>
    </x-slot>

    <x-mfw-support::validation-errors/>
    <x-mfw-support::response-messages/>

    <div class="shadow p-4 bg-body-tertiary rounded">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>{{ __('mfw::mfw-users.role_groups.key') }}</th>
                    <th>{{ __('mfw::mfw-users.role_groups.label') }}</th>
                    <th>{{ __('mfw::mfw-users.role_groups.description') }}</th>
                    <th width="220">{{ __('mfw::mfw-users.role_groups.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td><code>{{ $group->key }}</code></td>
                        <td>{{ $group->label }}</td>
                        <td>{{ $group->description ?: '-' }}</td>
                        <td>
                            <ul class="mfw-actions">
                                <li>
                                    <x-mfw::edit-link :route="route('mfw.role-groups.edit', $group->id)"/>
                                </li>
                                <x-mfw::delete-modal-link
                                    reference="{{ $group->id }}"
                                    :title="__('mfw::mfw-users.role_groups.delete')"
                                />
                            </ul>
                            <x-mfw::modal
                                :route="route('mfw.role-groups.destroy', $group->id)"
                                :question="__('mfw::mfw-users.role_groups.delete_confirm')"
                                :title="__('mfw::mfw-users.role_groups.delete')"
                                reference="destroy_{{ $group->id }}"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">{{ __('mfw::mfw-users.role_groups.empty') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-backend-layout>
