<nav>
    <!-- Primary Navigation Menu -->
    <div>
        <!-- Logo -->
        <div class="d-flex justify-content-center align-items-center px-3 py-5" style="background: #1A1A1A">
            <a href="{{ route('panel.dashboard') }}">
                @if(is_file(public_path('media/logo.png')))
                    <img src="{{ asset('media/logo.png') }}" alt="{{ config('app.name') }}" class="img-fluid">
                @endif
            </a>
        </div>
        <div id="sidebar-menu" class="main_menu_side bg-white main_menu">
            <ul class="nav side-menu">

                <x-mfw::nav-header-link title="Accueil" icon="fas fa-chart-pie" :route="route('panel.dashboard')"/>
                {{--
                <li class="sh-blue-grey">
                    <x-mfw::nav-opening-header title="Clientèle" icon="fas fa-users"/>
                    <ul class="nav child_menu">
                        @foreach(\App\Enum\ClientType::translations() as $key => $item)
                            <x-mfw::nav-link :route="route('panel.accounts.index', $key)" :title="$item" class="sh-blue-grey"/>
                        @endforeach
                        <x-mfw::nav-link :route="route('panel.accounts.index', 'all')" title="Tous" class="sh-blue-grey"/>
                    </ul>
                </li>

                <li class="sh-blue-grey">
                    <x-mfw::nav-opening-header title="Divers" icon="fas fa-book-open"/>
                    <ul class="nav child_menu">
                        <x-mfw::nav-link :route="route('panel.dictionnary.index')" title="Dictionnaires"/>
                        <x-mfw::nav-link :route="route('panel.bank.index')" :title="trans_choice('bank.label',2)"/>
                        <x-mfw::nav-link :route="route('panel.mailtemplates.index')" title="Courriers types"/>
                    </ul>
                </li>
                --}}
                <x-mfw::nav-header-link title="Administrateurs" icon="fas fa-user-lock" :route="route('panel.users.index', 'super-admin')"/>
                <x-mfw::nav-header-link title="Paramètres" icon="fas fa-cogs" :route="route('mfw.settings.index')"/>
                @if (config('mfw.siteowner.active'))
                <x-mfw::nav-header-link title="Entreprise" icon="fas fa-vcard" :route="route('mfw.siteowner.index')"/>
                @endif
                @role('dev')
                <li class="sh-dark-grey">
                    <x-mfw::nav-opening-header title="Dev" icon="fas fa-code" class="sh-dark-grey"/>
                    <ul class="nav child_menu">
                        <x-mfw::nav-link :route="route('panel.roles', 'super-admin')" title="Rôles"/>
                        {{-- <x-mfw::nav-link icon="fas fa-file" :route="route('panel.meta.create_admin')" title="Contenu" class-name="sh-dev"/>--}}
                    </ul>
                </li>
                @endrole
            </ul>
        </div>
    </div>
</nav>
