<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Erreur de l'application
        </h2>
    </x-slot>

    <div class="py-12">


        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="shadow p-3 mb-5 bg-body-tertiary rounded">
                <x-mfw::response-messages/>
            </div>
        </div>
    </div>
</x-backend-layout>
