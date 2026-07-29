<x-backend-layout>
    <x-slot name="header">
        <h2>{{ $title }}</h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-secondary mx-2" href="{{ route('mfw.role-groups.index') }}">
                <i class="fa-solid fa-bars"></i>
                {{ __('mfw::mfw.goback') }}
            </a>
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
        <form method="post" action="{{ $route }}">
            @csrf
            @if($method)
                @method($method)
            @endif

            <div class="row">
                <div class="col-xl-12 mb-3">
                    <label class="form-label" for="role_group_key">{{ __('mfw::mfw-users.role_groups.key') }} *</label>
                    <input id="role_group_key" name="key" type="text" class="form-control" value="{{ old('key', $group->key) }}" placeholder="{{ __('mfw::mfw-users.role_groups.placeholder_key') }}" required>
                </div>
            </div>

            <x-mfw-translatables :model="$group" :pluck="['label', 'description']"/>

            <x-mfw::btn-save/>
        </form>
    </div>
</x-backend-layout>
