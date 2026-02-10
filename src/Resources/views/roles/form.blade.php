<x-backend-layout>
    <x-slot name="header">
        <h2>{{ $title }}</h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-secondary mx-2" href="{{ route('mfw.roles.index') }}">
                <i class="fa-solid fa-bars"></i>
                {{ __('mfw.goback') }}
            </a>
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
        <form method="post" action="{{ $route }}">
            @csrf
            @if($method)
                @method($method)
            @endif

            <div class="row">
                <div class="col-xl-6 mb-3">
                    <label class="form-label" for="role_key">{{ __('mfw-users.roles.key') }} *</label>
                    <input id="role_key" name="key" type="text" class="form-control" value="{{ old('key', $role->key) }}" placeholder="{{ __('mfw-users.roles.placeholder_key') }}" @readonly($role->is_system) required>
                </div>
                <div class="col-xl-6 mb-3">
                    <x-mfw-inputable::select
                        name="group_id"
                        :label="__('mfw-users.roles.group')"
                        :values="$groups"
                        :affected="(int) old('group_id', $role->group_id ?: ($defaultGroupId ?? 0))"
                        :nullable="false"
                    />
                </div>
                <div class="col-xl-6 mb-3">
                    <input type="hidden" name="is_system" value="0">
                    <x-mfw-inputable::checkbox
                        name="is_system"
                        :label="__('mfw-users.roles.system')"
                        :affected="(bool) old('is_system', $role->is_system)"
                        :switch="true"
                        :value="1"
                    />
                </div>
            </div>

            <x-mfw-translatables :model="$role" :pluck="['label']"/>

            <x-mfw::btn-save/>
        </form>
    </div>
</x-backend-layout>
