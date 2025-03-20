<x-backend-layout>

    <div id="event-dashboard-ajax-container" data-ajax="{{route('ajax')}}">
        <div class="messages"></div>

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('ui.nav.dashboard') }}
            </h2>
        </x-slot>


        <livewire:back.dashboard.events />


    </div>
</x-backend-layout>
