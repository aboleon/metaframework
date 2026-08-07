<?php

declare(strict_types=1);

namespace MetaFramework\Navigation;

use Illuminate\Support\Facades\Route;

final class PanelNavigation
{
    /**
     * @var array<int, NavigationItem>
     */
    private array $items = [];

    public function __construct()
    {
        $this->register(...$this->coreItems());
    }

    public function register(NavigationItem ...$items): self
    {
        $this->items = [...$this->items, ...$items];

        return $this;
    }

    public function extend(string $sectionKey, NavigationItem ...$items): self
    {
        foreach ($this->items as $index => $item) {
            if ($item->key !== $sectionKey) {
                continue;
            }

            if (! $item->isSection()) {
                $this->registerConfigurationWarning($sectionKey);

                return $this;
            }

            $this->items[$index] = $item->appendChildren(...$items);

            return $this;
        }

        $this->registerConfigurationWarning($sectionKey);

        return $this;
    }

    /**
     * @return array<int, NavigationItem>
     */
    public function items(): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (NavigationItem $item): bool => $item->isVisible()
                && (!$item->isSection() || $item->hasVisibleChildren()),
        ));
    }

    /**
     * @return array<int, NavigationItem>
     */
    private function coreItems(): array
    {
        $backend = trim((string) config('mfw.urls.backend', config('mfw.route', 'panel')), '/');

        return [
            NavigationItem::link(
                'dashboard',
                __('mfw::mfw.nav.dashboard'),
                'bi bi-house-door',
                static fn (): string => url($backend),
            ),
            NavigationItem::section(
                'administration',
                __('mfw::mfw.nav.administration'),
                'bi bi-gear',
                [
                    $this->routeItem(
                        'users',
                        ucfirst(trans_choice('mfw::mfw.user', 2)),
                        'bi bi-people',
                        config('mfw.navigation.users_route', 'mfw.users.index'),
                        config('mfw.navigation.users_route_parameters', config('mfw.navigation.users_role', 'super-admin')),
                    ),
                    $this->routeItem(
                        'messages',
                        __('mfw::mfw.nav.messages'),
                        'bi bi-envelope-open',
                        config('mfw.navigation.messages_route', 'publisher.mails.index'),
                    ),
                    $this->routeItem(
                        'log-viewer',
                        __('mfw::mfw.nav.log_viewer'),
                        'bi bi-journal-text',
                        config('mfw.navigation.log_viewer_route', 'panel.log-viewer.index'),
                    ),
                ],
            ),
            NavigationItem::link(
                'website',
                __('mfw::mfw.nav.website'),
                'bi bi-box-arrow-up-right',
                static fn (): string => url('/'),
                target: '_blank',
            ),
        ];
    }

    private function registerConfigurationWarning(string $sectionKey): void
    {
        $warningKey = "navigation-warning-{$sectionKey}";

        foreach ($this->items as $item) {
            if ($item->key === $warningKey) {
                return;
            }
        }

        $this->register(NavigationItem::notice(
            $warningKey,
            __('mfw::mfw.nav.navigation_section_missing', ['section' => $sectionKey]),
            'bi bi-exclamation-triangle-fill text-warning',
        ));
    }

    private function routeItem(
        string $key,
        string $label,
        string $icon,
        ?string $routeName,
        array|string|null $parameters = [],
    ): NavigationItem {
        return NavigationItem::link(
            $key,
            $label,
            $icon,
            static fn (): string => route((string) $routeName, $parameters),
            static fn (): bool => $routeName !== null && Route::has($routeName),
        );
    }
}
