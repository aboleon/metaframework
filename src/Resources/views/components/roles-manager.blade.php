<div class="row">
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('mfw-users.roles.create_title') }}</h5>
                <form method="post" action="{{ route('mfw.roles.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">{{ __('mfw-users.roles.key') }}</label>
                        <input type="text" name="key" class="form-control" placeholder="{{ __('mfw-users.roles.placeholder_key') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('mfw-users.roles.label') }}</label>
                        <input type="text" name="label[{{ app()->getLocale() }}]" class="form-control" placeholder="{{ __('mfw-users.roles.placeholder_label') }}" required>
                    </div>
                    <div class="mb-3">
                        <x-mfw-inputable::select
                            name="group_id"
                            :label="__('mfw-users.roles.group')"
                            :values="$groups->pluck('label','id')->all()"
                            :nullable="false"
                            :affected="$groups->first()?->id"
                        />
                    </div>
                    <input type="hidden" name="is_system" value="0">
                    <x-mfw-inputable::checkbox name="is_system" :label="__('mfw-users.roles.system')" :switch="true" :value="1"/>
                    <button type="submit" class="btn btn-success btn-sm">{{ __('mfw-users.roles.create') }}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('mfw-users.roles.list_title') }}</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th>{{ __('mfw-users.roles.key') }}</th>
                            <th>{{ __('mfw-users.roles.label') }}</th>
                            <th>{{ __('mfw-users.roles.group') }}</th>
                            <th>{{ __('mfw-users.roles.system') }}</th>
                            <th width="180">{{ __('mfw-users.roles.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->key }}</td>
                                <td>{{ $role->label }}</td>
                                <td>{{ $role->group?->label ?? '-' }}</td>
                                <td>{{ $role->is_system ? __('mfw.yes') : __('mfw.no') }}</td>
                                <td>
                                    <a href="{{ route('mfw.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                                        {{ __('mfw-users.roles.edit') }}
                                    </a>
                                    @if(!$role->is_system)
                                        <form method="post" action="{{ route('mfw.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('{{ __('mfw-users.roles.delete_confirm') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('mfw-users.roles.delete') }}</button>
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
        </div>
    </div>
</div>
