<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ count($data) . ' ' . trans_choice('ui.role', count($data)) }}
        </h2>
    </x-slot>
    <div class="py-12">

        <div class="max-w-12xl mx-auto sm:px-6 lg:px-8">
            <div class="shadow p-3 mb-5 bg-body-tertiary rounded p-4">

                <x-mfw::response-messages/>
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('ui.title') }}</th>
                        <th>Clé</th>
                        <th>Type</th>
                        <th>Utilisateurs</th>
                    </tr>
                    </thead>
                    <tbody>

                    @forelse($data as $key=>$item)
                        <tr>
                            <td>{{ $item['id'] }}</td>
                            <th>{{ $data[$key]['label'] }}</th>
                            <td>{{ $key }}</td>
                            <td>{{ $item['profile'] }}</td>
                            <td>
                                <a class="btn btn-secondary btn-sm" href="{{ route('panel.users.index', $key) }}">{{ $roles[$item['id']] ?? 0  }}</a>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-backend-layout>
