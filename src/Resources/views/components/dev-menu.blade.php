@role('dev|super-admin')
    <li class="mfw-nav-item mfw-nav-dev-menu" data-ajax="{{ route('mfw.ajax') }}">
        <a href="#" class="mfw-nav-link">
            <i class="bi bi-code-slash"></i>
            <span>{{ __('mfw::mfw.nav.dev') }}</span>
            <i class="bi bi-chevron-down mfw-nav-chevron"></i>
        </a>
        <ul class="mfw-nav-submenu">
            <li class="mfw-nav-subitem">
                <x-mfw::simple-modal
                    class="mfw-nav-sublink"
                    id="mfw-nav-dev-artisan"
                    :title="__('mfw::mfw.dev.artisan.title')"
                    :text="'<i class=\'bi bi-terminal\'></i> '.__('mfw::mfw.dev.artisan.menu')"
                    :body="e(view('mfw::components.dev-artisan-command-form')->render())"
                    :confirm="__('mfw::mfw.dev.artisan.submit')"
                    :cancel="__('mfw::mfw.cancel')"
                    confirmclass="btn-warning mfw-nav-dev-artisan-confirm"
                    callback="ajaxRunMfwDevArtisanCommand"
                />
            </li>
            <li class="mfw-nav-subitem">
                <x-mfw::simple-modal
                    class="mfw-nav-sublink"
                    id="mfw-nav-dev-migrate"
                    :title="__('mfw::mfw.migrate_confirm_title')"
                    text="<i class='bi bi-database'></i> {{ __('mfw::mfw.nav.migration_db') }}"
                    :body="__('mfw::mfw.migrate_confirm_message')"
                    :confirm="__('mfw::mfw.confirm')"
                    :cancel="__('mfw::mfw.cancel')"
                    confirmclass="btn-warning mfw-nav-dev-migrate-confirm"
                    callback="ajaxConfirmMfwDevMigrationFromModal"
                />
            </li>
            <li class="mfw-nav-subitem">
                <x-mfw::simple-modal
                    class="mfw-nav-sublink"
                    id="mfw-nav-dev-migrate-rollback"
                    :title="__('mfw::mfw.migrate_rollback_confirm_title')"
                    text="<i class='bi bi-arrow-counterclockwise'></i> {{ __('mfw::mfw.nav.migration_rollback') }}"
                    :body="__('mfw::mfw.migrate_rollback_confirm_message')"
                    :confirm="__('mfw::mfw.confirm')"
                    :cancel="__('mfw::mfw.cancel')"
                    confirmclass="btn-warning mfw-nav-dev-migrate-rollback-confirm"
                    callback="ajaxConfirmMfwDevMigrationFromModal"
                />
            </li>
            <li class="mfw-nav-subitem">
                <a href="#" data-action="artisanOptimize" class="mfw-nav-dev-action mfw-nav-sublink">
                    <i class="bi bi-arrow-clockwise"></i> {{ __('mfw::mfw.nav.optimize') }}
                </a>
            </li>
            <li class="mfw-nav-subitem">
                <a href="#" data-action="restartQueueWorkers" class="mfw-nav-dev-action mfw-nav-sublink">
                    <i class="bi bi-arrow-repeat"></i> {{ __('mfw::mfw.dev.queue_restart') }}
                </a>
            </li>
            @if (app()->environment('local'))
                <li class="mfw-nav-subitem">
                    <a href="#" data-action="composerUpdateDev" class="mfw-nav-dev-action mfw-nav-sublink">
                        <i class="bi bi-shuffle"></i> {{ __('mfw::mfw.nav.composer_update_dev') }}
                    </a>
                </li>
            @endif
            @if (app()->environment('production'))
                <li class="mfw-nav-subitem">
                    <a href="#" data-action="composerInstallProd" class="mfw-nav-dev-action mfw-nav-sublink">
                        <i class="bi bi-arrow-down"></i> {{ __('mfw::mfw.nav.composer_install_prod') }}
                    </a>
                </li>
                <li class="mfw-nav-subitem">
                    <a href="#" data-action="composerUpdateProd" class="mfw-nav-dev-action mfw-nav-sublink">
                        <i class="bi bi-shuffle"></i> {{ __('mfw::mfw.nav.composer_update_prod') }}
                    </a>
                </li>
            @endif
            <li class="mfw-nav-subitem">
                <a href="#" data-action="composerDumpAutoload" class="mfw-nav-dev-action mfw-nav-sublink">
                    <i class="bi bi-gear-wide-connected"></i> {{ __('mfw::mfw.nav.composer_dump_autoload') }}
                </a>
            </li>
            {{ $slot ?? '' }}
        </ul>
    </li>
    @push('js')
        <script src="{{ asset('vendor/mfw/js/dev-menu.js') }}"></script>
    @endpush
@endrole
