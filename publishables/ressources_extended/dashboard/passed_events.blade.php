<x-backend-layout>

    <div>

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ui.nav.dashboard')  }}
            </h2>
        </x-slot>

        <livewire:back.dashboard.events-passed />

    </div>
</x-backend-layout>
