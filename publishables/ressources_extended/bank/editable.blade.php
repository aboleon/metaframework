<x-backend-layout>
    <x-slot name="header">
        <h2>
            {{ trans_choice('ui.dictionnary.label',2) }}
        </h2>

        <x-back.topbar.edit-combo
                route-prefix="panel.bank"
                :model="$data"
                :item-name="fn($m) => 'le compte ' . $data->name"
        />
    </x-slot>


    @php
        $error = $errors->any();
    @endphp

    <div class="shadow p-4 bg-body-tertiary rounded">
        <h2 class="legend">{!! $label ?? '' !!}</h2>
        <form method="post" action="{{ $route }}" id="wagaia-form">
            @csrf
            @if($data->id)
                @method('put')
            @endif

            <div class="row">
                @foreach($data->fillables as $name => $value)
                    <div class="col-lg-6 mb-3">
                        <x-mfw::input name="bank[{{ $name}}]"
                                      :value="$error ? old('bank.'.$name) : $data->{$name}"
                                      :label="$value['label']" />
                    </div>
                @endforeach
            </div>

        </form>
    </div>

    @push('js')
        <script>
          activateEventManagerLeftMenuItem('banks');
        </script>
    @endpush
</x-backend-layout>
