<li class="sh-dark-grey">
    <x-mfw::nav-opening-header title="MAINTENANCE" icon="fas fa-code" class="sh-dark-grey"/>
    <ul class="nav child_menu">
        {{-- @role('dev') --}}
        <x-mfw::nav-link :route="route('panel.roles', 'super-admin')" title="Rôles"/>
        <li>
            <a href="#" id="migrate-app">Migration DB</a>
        </li>
        <li>
            <a href="#" id="migrate-rollback">Migration Rollback DB</a>
        </li>
        {{-- @endrole --}}
        <li>
            <a href="#" id="reset-app">Réinitialiser App</a>
        </li>
        <li>
            <a href="#" id="composer-u-dev">Composer Update (dev)</a>
        </li>
        <li>
            <a href="#" id="composer-u-prod">Composer Update (prod)</a>
        </li>
    </ul>
</li>
@push('js')
    <script>
        $(function () {
            let resetAppContainer = $('body'), mfwmessages = $('#mfw-messages');
            mfwmessages.attr('data-ajax', '{{ route('mfw.ajax') }}');
            $('#reset-app').off().click(function (event) {
                event.preventDefault();
                setVeil(resetAppContainer);
                mfwAjax('action=artisanOptimize&callback=removeVeil', mfwmessages);
            });
            $('#migrate-app, #migrate-rollback').off().click(function (event) {
                event.preventDefault();
                setVeil(resetAppContainer);
                let rollback = $(this).is('#migrate-rollback') ? 1 : 0;
                mfwAjax('action=artisanMigrate&callback=removeVeil&rollback=' + rollback, mfwmessages);
            });
            $('#composer-u-dev').off().click(function (event) {
                event.preventDefault();
                setVeil(resetAppContainer);
                mfwAjax('action=composerUpdateDev&callback=removeVeil', mfwmessages);
            });
            $('#composer-u-prod').off().click(function (event) {
                event.preventDefault();
                setVeil(resetAppContainer);
                mfwAjax('action=composerUpdateProd&callback=removeVeil', mfwmessages);
            });
        });
    </script>
@endpush
