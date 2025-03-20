<form method="post" action="{{ $route . (isset($event_contact_id) ? '?event_contact_id='.$event_contact_id : '') }}" id="wagaia-form" novalidate>

    @csrf
    <x-mfw::tab-redirect/>


    @if(isset($redirect_to))
        <input type="hidden" name="custom_redirect" value="{{ $redirect_to }}">
    @endif

    @if ($provenance)
        <input type="hidden"
               name="provenance"
               value="{{ $provenance }}"/>
    @endif

    @if($account->id)
        @method('put')
    @else
        <input type="hidden"
               name="profile[created_by]"
               value="{{auth()->user()->id}}"/>
    @endif


    @if (isset($associate_event))
        <input type="hidden" name="associate_event" value="{{ $associate_event }}"/>
    @endif

    @if ($participation_type_id)
        <input type="hidden"
               name="participation_type_id"
               value="{{ $participation_type_id }}"/>
    @endif

    @if (isset($intervention_id))
        <input type="hidden"
               name="intervention_id"
               value="{{ (int)$intervention_id }}"/>
    @endif

    @if ($session_id)
        <input type="hidden"
               name="session_id"
               value="{{ $session_id }}"/>
    @endif


    @if (isset($associate_group))
        <input type="hidden" name="associate_group" value="{{ $associate_group }}"/>
    @endif


    <fieldset class="position-relative">
        <legend class="d-flex justify-content-between align-items-end">
            Compte client {{ $account->names() }}

            <div class="d-sm-flex d-block">
                @if ($account->trashed())
                    <x-mfw::notice message="Contact archivé" class="bg-danger text-white"/>
                @endif
                @if($account->id)
                    <x-mfw::notice message="Fiche créée {{ \App\Printers\UserRelated::creator($account) }}"/>
                @endif
            </div>
        </legend>
        <x-tab-cookie-redirect id="contact" selector="#account-nav-tab .mfw-tab"/>


        <nav class="d-flex justify-content-between" id="account-nav-tab">
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <x-mfw::tab tag="identity-tabpane" label="Fiche" :active="true"/>
                <x-mfw::tab tag="address-tabpane" label="Adresses & Contacts"/>
                <x-mfw::tab tag="documents-tabpane" label="Documents"/>
                <x-mfw::tab tag="passport-tabpane" label="Pièces d'identité"/>
                <x-mfw::tab tag="cards-tabpane" label="Cartes de fidélité"/>
                <x-mfw::tab tag="login-tabpane" label="Connexion"/>
                <x-mfw::tab tag="history-tabpane" label="Historique"/>
                <x-mfw::tab tag="associations-tabpane" label="Associations"/>
            </div>
        </nav>

        {{-- Identity --}}
        <div class="tab-content mt-3" id="nav-tabContent">

            {{-- Fiche --}}
            @include('accounts.tabs.identity')

            {{-- User passport --}}
            @include('accounts.tabs.passport')

            {{-- User documents --}}
            @include('accounts.tabs.documents')

            {{-- Fidelity cards --}}
            @include('accounts.tabs.cards')

            {{-- Login info --}}
            <div class="tab-pane fade"
                 id="login-tabpane"
                 role="tabpanel"
                 aria-labelledby="login-tabpane-tab">
                <h4 class="mt-4">Adresse de connexion</h4>
                @if ($account->id)
                    <x-mfw::notice :message="$account->email"/>
                @endif
                <br><br>
                @include('users.form.password')
                @include('accounts.form.blacklist')
            </div>

            {{-- Contacts --}}
            @include('accounts.tabs.address')

            {{-- History --}}
            @include('accounts.tabs.history')

            {{-- Bindings --}}
            @include('accounts.tabs.associations')
        </div>
    </fieldset>

</form>

@push('modals')
    @include('mfw-modals.launcher')
@endpush

@pushonce('js')
    <script src="{!! asset('js/dynamic_dictionnary.js') !!}"></script>
    <script src="{!! asset('js/contacts.js') !!}"></script>
    <script>
        let n = $('#user_first_name'),
            f = $('#user_last_name'),
            pn = $('#profile_passport_first_name'),
            pf = $('#profile_passport_last_name');
        contacts.format(n);
        contacts.format(f);
        @if (!$account->id)
        contacts.autocomplete(n, pn);
        contacts.autocomplete(f, pf);
        @else
        contacts.format(pn);
        contacts.format(pf);
        @endif
    </script>
@endpushonce
