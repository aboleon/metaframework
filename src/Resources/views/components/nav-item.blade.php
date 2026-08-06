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
