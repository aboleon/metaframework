<div class="row">
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('mfw-users.roles.create_title') }}</h5>
                <form method="post" action="{{ route('mfw.roles.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">{{ __('mfw-users.roles.slug') }}</label>
                        <input type="text" name="slug" class="form-control" placeholder="{{ __('mfw-users.roles.placeholder_slug') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('mfw-users.roles.label') }}</label>
                        <input type="text" name="label" class="form-control" placeholder="{{ __('mfw-users.roles.placeholder_label') }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('mfw-users.roles.group') }}</label>
                        <input type="text" name="group_key" class="form-control" value="admin">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{ __('mfw-users.roles.profile') }}</label>
                        <input type="text" name="profile" class="form-control" value="admin">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('mfw-users.roles.subgroup') }}</label>
                        <input type="text" name="subgroup" class="form-control" value="admin">
                    </div>
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
                            <th>{{ __('mfw-users.roles.slug') }}</th>
                            <th>{{ __('mfw-users.roles.label') }}</th>
                            <th>{{ __('mfw-users.roles.group') }}</th>
                            <th>{{ __('mfw-users.roles.profile') }}</th>
                            <th>{{ __('mfw-users.roles.subgroup') }}</th>
                            <th width="180">{{ __('mfw-users.roles.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($roles as $role)
                            @if($role->is_system)
                                <tr>
                                    <td>{{ $role->slug }}</td>
                                    <td>{{ $role->label }}</td>
                                    <td>{{ $role->group_key }}</td>
                                    <td>{{ $role->profile }}</td>
                                    <td>{{ $role->subgroup }}</td>
                                    <td>
                                        <span class="badge text-bg-secondary">{{ __('mfw-users.roles.system') }}</span>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="6">
                                        <form method="post" action="{{ route('mfw.roles.update', $role) }}" class="row g-2 align-items-end">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-12 col-md-2">
                                                <label class="form-label mb-1">{{ __('mfw-users.roles.slug') }}</label>
                                                <input type="text" name="slug" class="form-control form-control-sm" value="{{ $role->slug }}" required>
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label mb-1">{{ __('mfw-users.roles.label') }}</label>
                                                <input type="text" name="label" class="form-control form-control-sm" value="{{ $role->label }}" required>
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label mb-1">{{ __('mfw-users.roles.group') }}</label>
                                                <input type="text" name="group_key" class="form-control form-control-sm" value="{{ $role->group_key }}">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label mb-1">{{ __('mfw-users.roles.profile') }}</label>
                                                <input type="text" name="profile" class="form-control form-control-sm" value="{{ $role->profile }}">
                                            </div>
                                            <div class="col-12 col-md-2">
                                                <label class="form-label mb-1">{{ __('mfw-users.roles.subgroup') }}</label>
                                                <input type="text" name="subgroup" class="form-control form-control-sm" value="{{ $role->subgroup }}">
                                            </div>
                                            <div class="col-12 col-md-2 text-md-end">
                                                <button type="submit" class="btn btn-primary btn-sm">{{ __('mfw-users.roles.save') }}</button>
                                            </div>
                                        </form>
                                        <form method="post" action="{{ route('mfw.roles.destroy', $role) }}" class="mt-2 text-end" onsubmit="return confirm('{{ __('mfw-users.roles.delete_confirm') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('mfw-users.roles.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6">{{ __('mfw-users.roles.empty') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
