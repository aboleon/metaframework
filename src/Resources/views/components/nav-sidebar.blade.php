<aside id="{{ $id }}" class="mfw-nav-sidebar">
    <div id="mfw-nav-logo" class="p-3 bg-light">
        <a href="{{ url(trim((string) config('mfw.urls.backend', config('mfw.route', 'panel')), '/')) }}">
            <img loading="lazy" alt="{{ $logoAlt }}" src="{{ $logoUrl }}" class="img-fluid" />
        </a>
        <button class="mfw-nav-mobile-close" type="button" aria-label="{{ trans('ui.nav.close') }}" hidden>
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>

    <nav id="mfw-nav-menu-wrap" class="hidden-print">
        <div class="mfw-nav-section">
            <ul id="mfw-nav-menu" class="mfw-nav-menu">
                @foreach ($items as $item)
                    @if ($item->isNotice())
                        <li class="mfw-nav-item mfw-nav-notice" role="status">
                            <span class="mfw-nav-link" title="{{ $item->label }}">
                                <i class="{{ $item->icon }}" aria-hidden="true"></i>
                                <span>{{ $item->label }}</span>
                            </span>
                        </li>
                    @elseif ($item->isSection())
                        <li class="mfw-nav-item">
                            <a href="#" class="mfw-nav-link">
                                <i class="{{ $item->icon }}"></i>
                                <span>{{ $item->label }}</span>
                                <i class="bi bi-chevron-down mfw-nav-chevron"></i>
                            </a>
                            <ul class="mfw-nav-submenu">
                                @foreach ($item->visibleChildren() as $child)
                                    <li class="mfw-nav-subitem">
                                        <a href="{{ $child->url() }}"
                                           class="mfw-nav-sublink"
                                           target="{{ $child->target }}"
                                           @if ($child->target === '_blank') rel="noopener" @endif>
                                            <i class="{{ $child->icon }}"></i> {{ $child->label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="mfw-nav-item">
                            <a href="{{ $item->url() }}"
                               class="mfw-nav-link"
                               target="{{ $item->target }}"
                               @if ($item->target === '_blank') rel="noopener" @endif>
                                <i class="{{ $item->icon }}"></i>
                                <span>{{ $item->label }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach

                {{ $slot }}

                <x-mfw::dev-menu>
                    {!! $devExtensions ?? '' !!}
                </x-mfw::dev-menu>
            </ul>
        </div>
    </nav>
</aside>
