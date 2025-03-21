<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-mfw::input :label="__('mfw-auth.first_name')"
                          name="first_name"
                          :value="old('first_name')"
                          :required="true"/>
        </div>
        <div>
            <x-mfw::input :label="__('mfw-auth.last_name')"
                          name="last_name"
                          :value="old('last_name')"
                          :required="true"/>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-mfw::input type="email"
                          :label="__('mfw-auth.email')"
                          name="email" :value="old('email')"
                          :required="true"/>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-mfw::input type="password"
                          :label="__('mfw-auth.password.label')"
                          name="password"
                          :required="true"
                          :params="['autocomplete'=>'new-password']"
            />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-mfw::input type="password"
                          :label="__('mfw-auth.password.confirm')"
                          name="password_confirmation"
                          :required="true"
                          :params="['autocomplete'=>'new-password']"
            />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a href="{{ route('login') }}">
                {{ __('mfw-auth.already_registered') }}
            </a>

            <button class="btn btn-sm btn-dark">
                {{ __('mfw-auth.register_account') }}
            </button>
        </div>
    </form>
</x-guest-layout>
