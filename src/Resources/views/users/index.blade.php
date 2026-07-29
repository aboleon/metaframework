<x-backend-layout>
    <x-slot name="header">
        <h2>
            {{ __('mfw::mfw-users.users.index_title', ['role' => $roleLabel]) }}
        </h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-success" href="{{ $showAllSystemUsers ? route('mfw.users.create_type') : route('mfw.users.create_type', $role) }}">
                <i class="fa-solid fa-circle-plus"></i>
                {{ __('mfw::mfw-users.users.add') }}
            </a>
        </div>
    </x-slot>

    <div class="wg-tabs nav nav-tabs mb-3">
        <a href="{{ route('mfw.users.index', $role) }}" class="nav-link tab @if(!$archived) active @endif">
            {{ __('mfw::mfw.active') }}
        </a>
        @if($supportsSoftDeletes)
            <a href="{{ route('mfw.users.archived', $role) }}" class="nav-link tab @if($archived) active @endif">
                {{ __('mfw::mfw.archived') }}
            </a>
        @endif
    </div>

    @php
        $colspan = 1;
        $resolveRoleBadge = static function (?string $key): array {
            $normalized = strtolower((string) $key);

            if ($normalized === 'super-admin') {
                return ['class' => 'text-white', 'style' => 'background-color:#b42757;'];
            }

            if ($normalized === 'dev') {
                return ['class' => 'bg-dark text-white', 'style' => ''];
            }

            return ['class' => 'bg-secondary text-white', 'style' => ''];
        };

        if ($columns['first_name'] && $columns['last_name']) {
            $colspan += 2;
        } elseif ($columns['name']) {
            $colspan += 1;
        }
        if ($columns['email']) {
            $colspan += 1;
        }
        $colspan += 1; // roles
        $colspan += 1; // last login
    @endphp

    <div class="shadow p-4 bg-body-tertiary rounded">
        <x-mfw-support::response-messages/>

        <table class="table table-hover">
            <thead>
            <tr>
                @if($columns['first_name'] && $columns['last_name'])
                    <th>{{ __('mfw::mfw-users.users.first_name') }}</th>
                    <th>{{ __('mfw::mfw-users.users.last_name') }}</th>
                @elseif($columns['name'])
                    <th>{{ __('mfw::mfw-users.users.name') }}</th>
                @endif
                @if($columns['email'])
                    <th>{{ __('mfw::mfw-users.users.email') }}</th>
                @endif
                <th>{{ __('mfw::mfw-users.users.table_roles') }}</th>
                <th>{{ __('mfw::mfw-users.users.last_login') }}</th>
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
                    <td>
                        @if(method_exists($user, 'roles') && $user->roles->isNotEmpty())
                            @foreach($user->roles as $userRole)
                                @php($badge = $resolveRoleBadge($userRole->role?->key))
                                <span class="badge {{ $badge['class'] }}" @if($badge['style'] !== '') style="{{ $badge['style'] }}" @endif>{{ $userRole->role?->label ?? $userRole->role_id }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $user->last_login_at?->format('d.m.Y H:i') ?? '-' }}</td>
                    <td class="text-end">
                        @include('mfw::users.partials.actions', ['data' => $user, 'role' => $role, 'supportsSoftDeletes' => $supportsSoftDeletes])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colspan }}" class="text-center text-muted">{{ __('mfw::mfw.no_data_provided') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $users->links() }}
    </div>
</x-backend-layout>
