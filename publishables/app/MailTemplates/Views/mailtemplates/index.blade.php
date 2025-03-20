<x-backend-layout>

    @push('css')
        <style>
            td.phone span {
                display: block;
            }
        </style>
    @endpush

    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Courriers type &raquo;
            <a class="btn btn-sm btn-success"
               href="{{ route('panel.mailtemplates.create') }}">Créer</a>

        </h2>
    </x-slot>
        <div class="shadow p-4 bg-body-tertiary rounded">
            <x-mfw::response-messages />
            {!! $dataTable->table()  !!}
        </div>
        @include('lib.datatable')
        @push('js')
            {{ $dataTable->scripts() }}
        @endpush
</x-backend-layout>
