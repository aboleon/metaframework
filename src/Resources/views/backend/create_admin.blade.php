<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Créer un contenu
        </h2>
    </x-slot>


    <div class="shadow p-4 bg-body-tertiary rounded">

        <x-mfw::validation-banner/>
        <x-mfw::response-messages/>
        <form method="post" action="{{ route('mfw.meta.create_admin') }}" class="p-4">
            @csrf
            <x-mfw-input::input name="type" label="Type"/>
            <div class="mt-5 main-save">
                <x-mfw::btn-save/>
            </div>
        </form>
    </div>
</x-backend-layout>
