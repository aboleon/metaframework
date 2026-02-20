<x-auth-layout>
    @push('css')
        {!!  csscrush_tag(public_path('front/css/auth.css')) !!}
    @endpush
    <!-- Session Status -->
    <x-mfw::auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        @if(is_file(public_path('media/logo.png')))
            <div class="text-center">
                <img src="{{ url('media/logo.png') }}" alt="{{ config('app.name') }}"  class="logo rounded-3 mb-5 w-50">
            </div>
        @endif

        <div>
            <x-mfw-inputable::input type="email"
                                    :label="__('mfw-auth.email')"
                                    name="email" :value="old('email')"
                                    :required="true"/>
        </div>

        <div class="my-4">
            <x-mfw-inputable::input type="password"
                                    :label="__('mfw-auth.password.label')"
                                    name="password" :value="old('email')"
                                    :required="true"
                                    :params="['autocomplete'=>'current-password']"
            />
        </div>

        <x-mfw-inputable::checkbox name="remember" :label="__('mfw-auth.keepMe')" value="1" :affected="old('remember')"/>


        <div class="d-flex align-items-center justify-content-between mt-4">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">
                    {{ __('mfw-auth.password.forgotten.label') }}
                </a>
            @endif

            <button class="btn btn-sm btn-dark">
                {{ __('mfw-auth.loginBtn') }}
            </button>
        </div>
    </form>
</x-auth-layout>
