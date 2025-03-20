<x-backend-layout>
    <x-slot name="header">
        <h2>
            Types de participation
        </h2>
        <x-back.topbar.list-combo route-prefix="panel.participationtypes" />
    </x-slot>

    <div class="shadow p-4 bg-body-tertiary rounded">
        <x-datatables-mass-delete model="Dictionnary"/>
        {!! $dataTable->table()  !!}
    </div>
    @include('templates.devmark')
    @include('lib.datatable')
    @push('js')
        {{ $dataTable->scripts() }}


    @endpush
</x-backend-layout>
