<x-backend-layout>
    <x-slot name="header">
        <h2>{{ __('mfw-users.roles.title') }}</h2>
    </x-slot>

    <x-mfw-support::validation-errors/>
    <x-mfw-support::response-messages/>

    <x-mfw::roles-manager :roles="$roles"/>
</x-backend-layout>
