<x-guest-layout>
    <div class="mb-4">
        {{ __('mfw-auth.verification_msg') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4">
            {{ __('mfw-auth.verification_link_sent_msg') }}
        </div>
    @endif

    <div class="mt-4 flex align-items-center justify-content-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <button type="submit" class="btn btn-sm btn-dark">
                    {{ __('mfw-auth.resend_verification_link') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-dark">
                {{ __('mfw-auth.logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>
