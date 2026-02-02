<li class="sh-dark-grey">
    <x-mfw::nav-opening-header title="MAINTENANCE" icon="fas fa-code" class="sh-dark-grey"/>
    <ul class="nav child_menu">
        @role('dev')
        <x-mfw::nav-link :route="route('panel.roles', 'super-admin')" title="Rôles"/>
        {{-- <x-mfw::nav-link icon="fas fa-file" :route="route('panel.meta.create_admin')" title="Contenu" class-name="sh-dev"/>--}}
        <li>
            <a href="#" id="migrate-app">Migration DB</a>
        </li>
        <li>
            <a href="#" id="migrate-rollback">Migration Rollback DB</a>
        </li>
        @endrole
        <li>
            <a href="#" id="reset-app">Réinitialiser App</a>
        </li>
        {{--
        <li>
            <a href="#" id="composer-u">Composer Update</a>
        </li>
        --}}
    </ul>
</li>
@push('js')
    <script>
        $(function () {
            let resetAppContainer = $('body'), mfwmessages = $('#mfw-messages');
            $('#reset-app').off().click(function () {
                setVeil(resetAppContainer);
                ajax('action=artisanOptimize&callback=removeSystemVeil', mfwmessages);
            });
            $('#migrate-app, #migrate-rollback').off().click(function () {
                setVeil(resetAppContainer);
                let rollback = $(this).is('#migrate-rollback') ? 1 : 0;
                ajax('action=artisanMigrate&callback=removeSystemVeil&rollback=' + rollback, mfwmessages);
            });
            {{--
                $('#composer-u').off().click(function () {
                    setVeil(resetAppContainer);
                    ajax('action=composerUpdate&callback=removeSystemVeil', mfwmessages);
                });

                --}}
        });
    </script>
@endpush
