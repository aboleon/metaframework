<x-backend-layout>
    <x-slot name="header">
        <h2>
            Comptes
        </h2>
        <x-back.topbar.edit-combo
                item-name="le compte {{ $account->names() }}"
                :model="$account"
                :index-route="route('panel.users.index', 'super-admin')"
                :create-route="route('panel.users.create_type', ['role' => 'super-admin'])"
                route_prefix="panel.users"
        />
    </x-slot>

    <x-mfw::validation-banner/>
    <x-mfw::validation-errors/>
    <x-mfw::response-messages/>

    <div class="shadow p-5 mb-5 bg-body-tertiary rounded">
        <form method="post" action="{{ $route }}" id="wagaia-form" novalidate>
            @csrf
            @if(isset($method))
                @method($method)
            @endif
            <fieldset>
                <div class="d-flex justify-content-between">
                    <legend class="float-none w-auto ">{{ $label }}</legend>
                    <div>
                        @if ($account->trashed())
                            <span class="mfw-status offline">Compte archivé</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="row gx-5 mb-4">
                        <div class="col-lg-6">
                            <h4>Identité</h4>
                            <div class="row">
                                @include('users.form.ad_nominem')
                            </div>
                            @include('users.form.roles')
                        </div>
                        <div class="col-lg-6">
                            <div class="row">
                                @include('users.form.password')
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            @include('users.form.profile')

        </form>
    </div>

    @push('js')
        <script>
          activateEventManagerLeftMenuItem('admins');
        </script>
    @endpush

</x-backend-layout>
