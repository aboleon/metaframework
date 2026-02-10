<x-backend-layout>
    <x-slot name="header">
        <h2>
            {{ __('mfw-users.users.index_title', ['role' => $roleLabel]) }}
        </h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-success" href="{{ route('mfw.users.create_type', $role) }}">
                <i class="fa-solid fa-circle-plus"></i>
                {{ __('mfw-users.users.add') }}
            </a>
        </div>
    </x-slot>

    <div class="wg-tabs nav nav-tabs mb-3">
        <a href="{{ route('mfw.users.index', $role) }}" class="nav-link tab @if(!$archived) active @endif">
            {{ __('mfw.active') }}
        </a>
        @if($supportsSoftDeletes)
            <a href="{{ route('mfw.users.archived', $role) }}" class="nav-link tab @if($archived) active @endif">
                {{ __('mfw.archived') }}
            </a>
        @endif
    </div>

    @php
        $colspan = 1;
        if ($columns['first_name'] && $columns['last_name']) {
            $colspan += 2;
        } elseif ($columns['name']) {
            $colspan += 1;
        }
        if ($columns['email']) {
            $colspan += 1;
        }
        $colspan += 1; // last login
    @endphp

    <div class="shadow p-4 bg-body-tertiary rounded">
        <x-mfw-support::response-messages/>

        <table class="table table-hover">
            <thead>
            <tr>
                @if($columns['first_name'] && $columns['last_name'])
                    <th>{{ __('mfw-users.users.first_name') }}</th>
                    <th>{{ __('mfw-users.users.last_name') }}</th>
                @elseif($columns['name'])
                    <th>{{ __('mfw-users.users.name') }}</th>
                @endif
                @if($columns['email'])
                    <th>{{ __('mfw-users.users.email') }}</th>
                @endif
                <th>{{ __('mfw-users.users.last_login') }}</th>
                <th width="160"></th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    @if($columns['first_name'] && $columns['last_name'])
                        <td>{{ $user->first_name }}</td>
                        <td>{{ $user->last_name }}</td>
                    @elseif($columns['name'])
                        <td>{{ $user->name }}</td>
                    @endif
                    @if($columns['email'])
                        <td>{{ $user->email }}</td>
                    @endif
                    <td>{{ $user->last_login_at?->format('d.m.Y H:i') ?? '-' }}</td>
                    <td class="text-end">
                        @include('mfw::users.partials.actions', ['data' => $user, 'role' => $role, 'supportsSoftDeletes' => $supportsSoftDeletes])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colspan }}" class="text-center text-muted">{{ __('mfw.no_data_provided') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $users->links() }}
    </div>
</x-backend-layout>
