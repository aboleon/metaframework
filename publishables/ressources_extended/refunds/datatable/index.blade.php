<x-event-manager-layout :event="$event">

    <x-slot name="header">
        <h2>
            Gestion des avoirs
        </h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            <a class="btn btn-sm btn-primary me-2"
               href="#">
                <i class="bi bi-box-arrow-in-up-right"></i>
                Exporter</a>
            <x-event-config-btn :event="$event"/>
            <div class="separator"></div>
        </div>
    </x-slot>

    <div class="shadow p-4 bg-body-tertiary rounded">
        <x-mfw::response-messages/>
        {!! $dataTable->table()  !!}
    </div>

    @include('lib.datatable')
    @push('js')
        {{ $dataTable->scripts() }}
        <script src="{{ asset('js/orders/send_refund_from_modal.js') }}"></script>
    @endpush

</x-event-manager-layout>
