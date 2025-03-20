<nav>
    <!-- Primary Navigation Menu -->
    <div>
        <!-- Logo -->
        <div class="d-flex justify-content-center align-items-center px-3 py-4" style="background: #1A1A1A">
            <a href="{{ route('panel.dashboard') }}">
                <x-jet-application-mark/>
            </a>
        </div>
        <div id="sidebar-menu" class="main_menu_side bg-white main_menu">
            <ul class="nav side-menu">

                <x-mfw::nav-header-link title="Accueil" icon="fas fa-chart-pie" :route="route('panel.dashboard')"/>
                <x-mfw::nav-header-link title="Administrateurs"
                                        icon="fas fa-user-lock"
                                        :route="route('panel.users.index', 'super-admin')"
                                        class="sh-blue-grey standalone-admins"
                />

                <li class="sh-blue-grey">
                    <x-mfw::nav-opening-header title="Contacts" icon="fas fa-users"/>
                    <ul class="nav child_menu">
                        <x-mfw::nav-link :route="route('panel.accounts.index', 'all')"
                                         title="Tous"
                                         className="sh-blue-grey item-accounts-all"/>
                        @foreach(\App\Enum\ClientType::translations() as $key => $item)
                            <x-mfw::nav-link :route="route('panel.accounts.index', $key)" :title="$item" className="sh-blue-grey item-accounts-{{$key}}"/>
                        @endforeach

                    </ul>
                </li>

                <x-mfw::nav-header-link :title="__('ui.groups')"
                                        icon="fas fa-user"
                                        :route="route('panel.groups.index')"
                                        class="sh-blue-grey standalone-groups"
                />
                <x-mfw::nav-header-link title="Hébergements"
                                        icon="fas fa-hotel"
                                        :route="route('panel.hotels.index')"
                                        class="sh-blue-grey standalone-hotels"
                />
                <x-mfw::nav-header-link :title="trans_choice('events.label',2)"
                                        icon="fas fa-bookmark"
                                        :route="route('panel.events.index')"
                                        class="sh-blue-grey standalone-events"
                />
                <x-mfw::nav-header-link :title="trans_choice('ui.places.label', 2)"
                                        icon="fas fa-earth"
                                        :route="route('panel.places.index')"
                                        class="sh-blue-grey standalone-places"
                />

                <x-mfw::nav-header-link :title="trans_choice('ui.establishments.label', 2)"
                                        icon="fas fa-building"
                                        :route="route('panel.establishments.index')"
                                        class="sh-blue-grey standalone-establishments"
                />


                <li class="sh-blue-grey">
                    <x-mfw::nav-opening-header title="Divers" icon="fas fa-book-open"/>
                    <ul class="nav child_menu">
                        <x-mfw::nav-link :route="route('panel.dictionnary.index')"
                                         title="Dictionnaires"
                                         className="item-dictionaries"
                        />
                        <x-mfw::nav-link :route="route('panel.participationtypes.index')"
                                         title="Types de participation"
                                         className="item-participation-types"
                        />
                        <x-mfw::nav-link :route="route('panel.bank.index')"
                                         :title="trans_choice('bank.label',2)"
                                         className="item-banks"
                        />
                        <x-mfw::nav-link :route="route('mfw.vat.index')"
                                         title="TVA"
                                         className="item-vat"
                        />
                        <x-mfw::nav-link :route="route('panel.mailtemplates.index')" title="Courriers types"/>
                        <x-mfw::nav-link :route="route('panel.sellables.index')"
                                         title="Catalogue exposants"
                                         className="item-sellables"
                        />

                        <x-mfw::nav-link :route="route('panel.doc-json-api')" title="Doc json-api"/>
                    </ul>
                </li>

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
