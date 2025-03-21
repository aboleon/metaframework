<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('mfw-auth.secured_access_area') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-mfw::input type="password"
                          :label="__('mfw-auth.password.label')"
                          name="password"
                          :required="true"
                          :params="['autocomplete'=>'current-password']"
            />
        </div>

        <div class="flex justify-end mt-4">
            <button class="btn btn-sm btn-dark">
                {{ __('mfw-auth.password.confirm') }}
            </button>
        </div>
    </form>
</x-guest-layout>
