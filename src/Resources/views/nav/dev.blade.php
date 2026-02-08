<li>
    <x-mfw::nav-opening-header title="MAINTENANCE" icon="fas fa-code"/>
    <ul class="nav child_menu">
        {{-- @role('dev') --}}
        <x-mfw::nav-link :route="route('panel.roles', 'super-admin')" title="Rôles"/>
        <li>
            <a href="#" class="mfw-dev-action" data-action="artisanMigrate">Migration DB</a>
        </li>
        <li>
            <a href="#" class="mfw-dev-action" data-action="artisanMigrate" data-param-rollback="1">Migration Rollback DB</a>
        </li>
        {{-- @endrole --}}
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
            if (mfwmessages.length) {
                mfwmessages.attr('data-ajax', '{{ route('mfw.ajax') }}');
            }
            $('.mfw-dev-action').off('click.mfwDevAction').click(function (event) {
                event.preventDefault();
                let action = $(this).data('action');
                if (!action) {
                    return;
                }

                let requestData = [
                    'action=' + encodeURIComponent(action),
                    'callback=removeVeil'
                ];

                $.each(this.attributes, function () {
                    if (!this.name || this.name.indexOf('data-param-') !== 0) {
                        return;
                    }
                    let paramName = this.name.substring('data-param-'.length);
                    if (!paramName || this.value === '') {
                        return;
                    }
                    requestData.push(encodeURIComponent(paramName) + '=' + encodeURIComponent(this.value));
                });

                setVeil(resetAppContainer);
                mfwAjax(requestData.join('&'), mfwmessages);
            });
        });
    </script>
@endpush
