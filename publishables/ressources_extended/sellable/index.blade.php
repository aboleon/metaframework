<x-backend-layout>

    <x-slot name="header">
        <h2>
            Catalogue exposants
        </h2>
        <x-back.topbar.list-combo route-prefix="panel.sellables" />
    </x-slot>


    <div class="wg-tabs nav nav-tabs">
        <a href="{{route('panel.sellables.index')}}" class="nav-link tab @if(!$archived) active @endif">{{__('ui.active')}}</a>
        <a href="{{route('panel.sellables.archived')}}" class="nav-link tab @if($archived) active @endif">{{__('ui.archived')}}</a>
    </div>
    <div class="shadow p-4 bg-body-tertiary rounded">


        {!! $dataTable->table()  !!}
    </div>
    @include('lib.datatable')
    @push('js')
        {{ $dataTable->scripts() }}
    @endpush
</x-backend-layout>
