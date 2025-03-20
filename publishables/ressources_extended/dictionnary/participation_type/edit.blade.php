<x-backend-layout>
    @php
        $error = $errors->any();
    @endphp
    <x-slot name="header">
        <h2>
            {{ trans_choice('ui.dictionnary.label',2) }}
        </h2>

        <x-back.topbar.edit-combo
                route-prefix="panel.participationtypes"
                :model="$data"
                :item-name="fn($model) => 'le type de participation ' . $model->name"
        />
    </x-slot>

    <x-mfw::validation-banner />

    <div class="shadow p-4 bg-body-tertiary rounded">

        <h2 class="legend">{!! $label ?? '' !!}</h2>
        <form method="post" action="{{ $route }}" id="wagaia-form">
            @csrf
            @if($data->id)
                @method('put')
            @endif

            <x-mfw::translatable-tabs :model="$data" :fillables="$data->fillables" />

            <x-mfw::select :values="\App\Enum\ParticipantType::translations()"
                           :affected="$error ? old('group') : $data->group"
                           label="Groupe *"
                           name="group" />

        </form>
    </div>
    @push('js')
        <script>
          activateEventManagerLeftMenuItem('participation-types');
        </script>
    @endpush

</x-backend-layout>
