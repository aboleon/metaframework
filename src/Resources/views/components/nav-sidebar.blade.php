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
            @php
                $administration = null;
                $website = null;
                $itemsBeforeWebsite = [];

                foreach ($items as $item) {
                    if ($item->key === 'administration') {
                        $administration = $item;

                        continue;
                    }

                    if ($item->key === 'website') {
                        $website = $item;

                        continue;
                    }

                    $itemsBeforeWebsite[] = $item;
                }
            @endphp
            <ul id="mfw-nav-menu" class="mfw-nav-menu">
                @foreach ($itemsBeforeWebsite as $item)
                    @include('mfw::components.nav-item', ['item' => $item])
                @endforeach

                {{ $slot }}

                @if ($website !== null)
                    @include('mfw::components.nav-item', ['item' => $website])
                @endif

                @if ($administration !== null)
                    @include('mfw::components.nav-item', ['item' => $administration])
                @endif

                <x-mfw::dev-menu>
                    {!! $devExtensions ?? '' !!}
                </x-mfw::dev-menu>
            </ul>
        </div>
    </nav>
</aside>
