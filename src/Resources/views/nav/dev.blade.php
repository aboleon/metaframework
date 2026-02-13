@role('dev|super-admin')
<li>
    <x-mfw::nav-opening-header title="MAINTENANCE" icon="fas fa-code"/>
    <ul class="nav child_menu">
        <x-mfw::nav-link :route="route('mfw.users.index', 'super-admin')" :title="__('mfw-users.users.nav')"/>
        <x-mfw::nav-link :route="route('mfw.role-groups.index')" :title="__('mfw-users.role_groups.nav')"/>
        <x-mfw::nav-link :route="route('mfw.roles.index')" :title="__('mfw-users.roles.nav')"/>
        <li>
            <a href="#" class="mfw-dev-action" data-action="artisanMigrate">Migration DB</a>
        </li>
        <li>
            <x-mfw::simple-modal
                    id="mfw-dev-migrate-rollback"
                    :title="__('mfw.migrate_rollback_confirm_title')"
                    text="<i class='fa fa-undo'></i> Migration Rollback DB"
                    :body="__('mfw.migrate_rollback_confirm_message')"
                    :confirm="__('mfw.confirm')"
                    :cancel="__('mfw.cancel')"
                    confirmclass="btn-warning mfw-dev-migrate-rollback-confirm"/>
        </li>
        <li>
            <a href="#" class="mfw-dev-action" data-action="artisanOptimize">Réinitialiser App</a>
        </li>
        @if (app()->environment('local'))
            <li>
                <a href="#" class="mfw-dev-action" data-action="composerUpdateDev">Composer Update (dev)</a>
            </li>
        @endif
        @if (app()->environment('production'))
            <li>
                <a href="#" class="mfw-dev-action" data-action="composerInstallProd">Composer Install (prod)</a>
            </li>
            <li>
                <a href="#" class="mfw-dev-action" data-action="composerUpdateProd">Composer Update (prod)</a>
            </li>
        @endif
        <li>
            <a href="#" class="mfw-dev-action" data-action="composerDumpAutoload">Composer Dump Autoload</a>
        </li>
    </ul>
</li>

@push('js')
    <script>
        $(function () {
            let resetAppContainer = $('body'), mfwmessages = $('#mfw-messages');

            let executeDevAction = function (element) {
                let action = element.data('action');
                if (!action) {
                    return;
                }

                let requestData = [
                    'action=' + encodeURIComponent(action),
                    'callback=removeVeil'
                ];

                setVeil(resetAppContainer);
                mfwAjax(requestData.join('&'), mfwmessages);
            };

            if (mfwmessages.length) {
                mfwmessages.attr('data-ajax', '{{ route('mfw.ajax') }}');
            }
            $('.mfw-dev-action').off().click(function (event) {
                event.preventDefault();
                executeDevAction($(this));
            });

            $('#mfw-simple-modal .btn-confirm').off('click.mfwDevRollbackConfirm').click(function (event) {
                if (!$(this).hasClass('mfw-dev-migrate-rollback-confirm')) {
                    return;
                }
                event.preventDefault();
                setVeil(resetAppContainer);
                mfwAjax('action=artisanMigrate&callback=removeVeil&rollback=1&confirmed=1', mfwmessages);
                let simpleModalEl = document.getElementById('mfw-simple-modal');
                if (simpleModalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    let simpleModalInstance = bootstrap.Modal.getInstance(simpleModalEl);
                    if (simpleModalInstance) {
                        simpleModalInstance.hide();
                    }
                }
            });
            });
    </script>
@endpush
@endrole
