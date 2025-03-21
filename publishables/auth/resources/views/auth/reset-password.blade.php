<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-mfw::input type="email"
                          :label="__('mfw-auth.email')"
                          name="email" :value="old('email', $request->email)"
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
                          :label="__('mfw-auth.password.new')"
                          name="password_confirmation"
                          :required="true"
                          :params="['autocomplete'=>'new-password']"
            />
        </div>

        <div class="flex items-center justify-end mt-4">
            <button class="btn btn-sm btn-dark">
                {{ __('mfw-auth.password.forgotten.reset_btn') }}
            </button>
        </div>
    </form>
</x-guest-layout>
