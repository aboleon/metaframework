<nav class="nav navbar-nav float-end">
    <ul class="m-0 p-0 list-unstyled" id="my-account">
        <li>
            <x-jet-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button id="user_avatar" class="p-0 border-0 bg-transparent">

                        <x-mediaclass::printer :model="Mediaclass::forModel(auth()->user())->first()"
                                               :default="asset('media/logo-black.png')"
                                               class="rounded-circle w-100"
                                               size="md"
                                               :responsive="false"
                                               :alt="Auth::user()->names()"/>
                    </button>
                </x-slot>

                <x-slot name="content">

                    <x-jet-dropdown-link href="{{ route('profile.show') }}">
                        {{ trans_choice('account.label', 1) }}
                    </x-jet-dropdown-link>

                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <x-jet-dropdown-link href="{{ route('api-tokens.index') }}">
                            {{ __('API Tokens') }}
                        </x-jet-dropdown-link>
                    @endif

                    <div class="border-top my-2 mx-4"></div>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-jet-dropdown-link href="{{ route('logout') }}"
                                             onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            {{ __('account.logout') }}
                        </x-jet-dropdown-link>
                    </form>
                </x-slot>
            </x-jet-dropdown>
        </li>
    </ul>
</nav>
