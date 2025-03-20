<x-backend-layout>
    <x-slot name="header">
        <h2>
            {{ trans_choice('ui.user', 2) . ' '.__('ui.with_role') . ' '. __('user_type.'.$role.'.label') }}
        </h2>
        <x-back.topbar.list-combo :create-route="route('panel.users.create_type', ['role' => $role])" />

    </x-slot>

    <div class="wg-tabs nav nav-tabs">
        <a href="{{route('panel.users.index', $role)}}"
           class="nav-link tab @if(!$archived) active @endif">{{__('ui.active')}}</a>
        <a href="{{route('panel.users.archived', $role)}}"
           class="nav-link tab @if($archived) active @endif">{{__('ui.archived')}}</a>
    </div>
    <div class="shadow p-4 bg-body-tertiary rounded">
        <x-mfw::response-messages />
        {!! $dataTable->table()  !!}
    </div>

    @include('lib.datatable')
    @push('js')
        {{ $dataTable->scripts() }}
    @endpush


    <div class="shadow p-4 bg-body-tertiary rounded mt-3">
        <form data-ajax="{{ route('ajax') }}" id="settings-form" class="d-flex align-items-end">

            <div class="w-100">
                <x-mfw::input name="admin_shared_address"
                              :value="old('admin_shared_address', $admin_shared_address)"
                              label="Adresse pour tous les administrateurs" />
            </div>
            <div>
                <button type="button" class="btn btn-warning ms-2 ">Enregistrer</button>
            </div>
        </form>
    </div>
    @push('js')
        <script>
          $('#settings-form button').click(function() {
            ajax('action=updateAdminAdressSettings&data=' + $('#admin_shared_address').val(), $('#settings-form'));
          });
        </script>
    @endpush

</x-backend-layout>
