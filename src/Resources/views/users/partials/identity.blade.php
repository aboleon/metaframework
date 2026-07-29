@if($columns['first_name'] && $columns['last_name'])
    <div class="col-xl-6 mb-3">
        <label class="form-label" for="user_first_name">{{ __('mfw::mfw-users.users.first_name') }} *</label>
        <input
            id="user_first_name"
            type="text"
            class="form-control"
            name="user[first_name]"
            value="{{ old('user.first_name', $account->first_name) }}"
            required
        />
    </div>
    <div class="col-xl-6 mb-3">
        <label class="form-label" for="user_last_name">{{ __('mfw::mfw-users.users.last_name') }} *</label>
        <input
            id="user_last_name"
            type="text"
            class="form-control"
            name="user[last_name]"
            value="{{ old('user.last_name', $account->last_name) }}"
            required
        />
    </div>
@elseif($columns['name'])
    <div class="col-lg-12 mb-3">
        <label class="form-label" for="user_name">{{ __('mfw::mfw-users.users.name') }} *</label>
        <input
            id="user_name"
            type="text"
            class="form-control"
            name="user[name]"
            value="{{ old('user.name', $account->name) }}"
            required
        />
    </div>
@endif

@if($columns['email'])
    <div class="col-lg-12 mb-3">
        <label class="form-label" for="user_email">{{ __('mfw::mfw-users.users.email') }} *</label>
        <input
            id="user_email"
            type="email"
            class="form-control"
            name="user[email]"
            value="{{ old('user.email', $account->email) }}"
            required
        />
    </div>
@endif
