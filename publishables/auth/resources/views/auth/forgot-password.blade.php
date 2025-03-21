<x-guest-layout>
    <div class="mb-4">
        {{ __('mfw-auth.password.forgotten.txt') }}
    </div>

    <!-- Session Status -->
    <x-mfw::auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <!-- Email Address -->
        <div>
            <x-mfw::input type="email"
                          :label="__('mfw-auth.email')"
                          name="email" :value="old('email')"
                          :required="true"/>
        </div>

        <div class="flex items-center justify-end mt-4">
            <button class="btn btn-sm btn-dark">
                {{ __('mfw-auth.password.forgotten.label') }}
            </button>
        </div>
    </form>
</x-guest-layout>
