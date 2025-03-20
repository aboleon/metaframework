<x-event-manager-layout :event="$event">

    <x-slot name="header">
        <h2>
            Gestion des commandes
        </h2>

        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-primary me-2"
               href="#">
                <i class="bi bi-box-arrow-in-up-right"></i>
                Exporter</a>
            <x-event-config-btn :event="$event"/>

            <div class="separator"></div>

            <a class="btn btn-sm btn-success ms-2"
               href="{{ route('panel.manager.event.orders.create', $event) }}">
                <i class="fa-solid fa-circle-plus"></i>
                Commande
            </a>
            <a class="btn btn-sm btn-warning ms-2 text-dark"
               href="{{ route('panel.manager.event.orders.create', ['event' => $event->id, 'as_orator']) }}">
                <i class="fa-solid fa-circle-plus"></i>
                Commande orateur
            </a>

            <div class="separator"></div>
        </div>

    </x-slot>

    <div class="shadow p-4 bg-body-tertiary rounded">
        <x-mfw::response-messages/>
        <x-datatables-mass-delete model="Order" name="title" question="<strong>Est-ce que vous confirmez la suppression de ces commandes ?</p>"/>
        {!! $dataTable->table()  !!}
    </div>

    @include('lib.datatable')
    @push('js')
        {{ $dataTable->scripts() }}
    @endpush

</x-event-manager-layout>
