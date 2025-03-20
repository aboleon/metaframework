<x-backend-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-align-items-center pe-5">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {!! $label !!} &raquo;
            </h2>
        </div>
    </x-slot>


    @if($dictionnary->id)
        @php
            $counter = $dictionnary->entries->count();
        @endphp
        <div class="counter d-flex justify-content-center align-items-center mb-3">
            <div class="text-center me-3 rounded py-1 px-3 border border-dark-subtle">
                <span style="font-size: 40px;color: var(--ab-blue-grey);" class="d-block fw-bold">{{ $counter }}</span>
                <small class="d-block" style="margin-top: -10px">{{ trans_choice('ui.dictionnary.entries', $counter) }}</small>
            </div>
            <div>
                @if ($dictionnary->id)
                    <a class="btn btn-sm btn-nav-blue"
                       href="{{ route('panel.dictionnary.entries.create', ['dictionnary'=> $dictionnary]) }}">Créer </a>
                    <br>
                    <a class="btn btn-sm btn-secondary mt-2"
                       href="{{ route('panel.dictionnary.index') }}">Index </a>
                @endif
            </div>
        </div>
    @endif

    <div class="bg-white shadow-xl sm:rounded-lg px-4 py-2 mb-4" style="margin: 0 -12px">

        <table class="table">
            <tr>
                <th>Entrée</th>
                @if (!$dictionnary->id)
                    <th>Dictionnaire</th>
                @endif
                <th>Entrées</th>
                <th>Position</th>
                <th style="width: 100px">Actions</th>
            </tr>
            <tbody>
            @forelse($dictionnary->entries as $item)
                <x-dico-row-looper :item="$item" :dictionnary="$dictionnary"/>
            @empty
                <tr>
                    <td colspan="7">
                        {{ __('errors.no_data_in_db') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <x-mfw::pagination :object="$dictionnary"/>

    </div>
</x-backend-layout>
