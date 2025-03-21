<x-guest-layout>

    @push('css')
        {!!  csscrush_tag(public_path('front/css/auth.css')) !!}
    @endpush

    @if(is_file(public_path('media/logo.png')))
        <a href="/">
            <img src="{{ url('media/logo.png') }}" class="logo" alt="{{ config('app.name') }}" style="max-width: 70px">
        </a>
    @endif

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <x-mfw::alert type="warning"
                              :message="__(str_replace('passwords.', 'auth.password.forgotten.',$error))"/>
            @endforeach
        </div>
    @endif

    <!-- Session Status -->
    <x-mfw::auth-session-status class="mb-4" :status="session('status')"/>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="row">
            <div class="col-2 offset-4">
                <div>
                    <x-mfw::input type="email"
                                  :label="__('mfw-auth.email')"
                                  name="email" :value="old('email')"
                                  :required="true"/>
                </div>

                <div class="mt-4">
                    <x-mfw::input type="password"
                                  :label="__('mfw-auth.password.label')"
                                  name="password" :value="old('email')"
                                  :required="true"
                                  :params="['autocomplete'=>'current-password']"
                    />
                </div>

                <x-mfw::checkbox name="remember" :label="__('mfw-auth.keepMe')" value="1" :affected="old('remember')"/>


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
            </div>
        </div>
    </form>


</x-guest-layout>
