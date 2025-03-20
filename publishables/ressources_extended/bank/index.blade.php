<x-backend-layout>
    <x-slot name="header">
        <h2>
            {{ count($data) .' '. trans_choice('bank.label',2) }}
        </h2>

        <x-back.topbar.list-combo route-prefix="panel.bank" />
    </x-slot>

    <div class="shadow p-4 bg-body-tertiary rounded">

        <table class="table">
            <thead>
            <tr>
                <th>{{ __('bank.name') }}</th>
                <th>{{ __('bank.account') }}</th>
                <th style="width:200px" class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>


            @forelse($data as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->account }}</td>
                    <td>
                        <ul class="mfw-actions">
                            <x-mfw::edit-link :route="route('panel.bank.edit', $item)"/>
                            @role('dev')
                            <x-mfw::delete-modal-link reference="{{ $item->id }}"/>
                            @endrole
                        </ul>
                        <x-mfw::modal :route="route('panel.bank.destroy', $item->id)"
                                      question="Supprimer {{ $item->name }} ?"
                                      reference="destroy_{{ $item->id }}"/>
                    </td>
                </tr>
            @empty
            @endforelse
            </tbody>
        </table>
    </div>
</x-backend-layout>
