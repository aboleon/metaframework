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
            $counter = $entries->total();
        @endphp
        <div class="counter d-flex justify-content-center align-items-center mb-3">
            <x-counter :count="$counter" :label="trans_choice('ui.dictionnary.entries', $counter)"/>
            <div>
                <a class="btn btn-sm btn-nav-blue"
                   href="{{ route('panel.dictionnary.entries.create', ['dictionnary'=> $dictionnary]) }}">Créer </a>
                <br>
                <a class="btn btn-sm btn-secondary mt-2"
                   href="{{ route('panel.dictionnary.index') }}">Index </a>
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
                <th>Position</th>
                <th style="width: 100px">Actions</th>
            </tr>
            <tbody>
            @forelse($entries as $item)
                @php
                    //$can_delete = (($item->trashed() && $item->subs_count < 1) or !$item->trashed());
                @endphp
                <tr>
                    <td>{{ $item->name }} {{ $item->deleted_at }}</td>
                    @if (!$dictionnary->id)
                        <td>
                            <a class="btn btn-xs btn-default"
                               href="{!! route('panel.dictionnary.entries.index', $item->dictionnary) !!}">{{ $item->dictionnary->name }}</a>
                        </td>
                    @endif
                    <td>{{ $item->position }}</td>
                    <td>
                        <ul class="mfw-actions">
                            <x-mfw::edit-link :route="route('panel.dictionnaryentry.edit', $item)"/>
                            <x-mfw::delete-modal-link reference="{{ $item->id }}"/>
                        </ul>
                        <x-mfw::modal :route="route('panel.dictionnaryentry.destroy', $item)"
                                      :question="'Supprimer ' . $item->name. '?'"
                                      reference="destroy_{{ $item->id }}"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        {{ __('errors.no_data_in_db') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <x-mfw::pagination :object="$entries"/>

    </div>
</x-backend-layout>
