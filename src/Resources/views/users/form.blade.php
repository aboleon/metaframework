<x-backend-layout>
    <x-slot name="header">
        <h2>{{ $label }}</h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-secondary mx-2" href="{{ route('mfw.users.index', $roleKey) }}">
                <i class="fa-solid fa-bars"></i>
                {{ __('mfw::mfw.goback') }}
            </a>
            <a class="btn btn-sm btn-success" href="{{ route('mfw.users.create_type', $roleKey) }}">
                <i class="fa-solid fa-circle-plus"></i>
                {{ __('mfw::mfw-users.users.add') }}
            </a>
        </div>
    </x-slot>

    <x-mfw-support::validation-banner/>
    <x-mfw-support::validation-errors/>
    <x-mfw-support::response-messages/>

    <div class="shadow p-4 bg-body-tertiary rounded">
        <form method="post" action="{{ $route }}">
            @csrf
            @if(isset($method))
                @method($method)
            @endif

            @if(method_exists($account, 'trashed') && $account->trashed())
                <div class="mb-3">
                    <span class="mfw-status offline">{{ __('mfw::mfw.account_archived') }}</span>
                </div>
            @endif

            <div class="row gx-5 mb-4">
                <div class="col-lg-6">
                    <h4>{{ __('mfw::mfw-users.users.identity') }}</h4>
                    <div class="row">
                        @include('mfw::users.partials.identity', ['account' => $account, 'columns' => $columns])
                    </div>

                    @include('mfw::users.partials.roles', [
                        'account' => $account,
                        'roles' => $roles,
                        'selectedRoleIds' => $selectedRoleIds,
                        'forcedRole' => $forcedRole,
                    ])
                </div>

                <div class="col-lg-6">
                    <div class="row">
                        @include('mfw::users.partials.password', ['account' => $account])
                    </div>
                </div>
            </div>

            <x-mfw::btn-save/>
        </form>
    </div>
</x-backend-layout>
