<?php

declare(strict_types=1);

namespace Tests\Unit\Navigation;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use MetaFramework\Navigation\NavigationItem;
use MetaFramework\Navigation\PanelNavigation;
use MetaFramework\ServiceProvider;
use Tests\TestCase;

class PanelNavigationTest extends TestCase
{
    public function test_core_navigation_contains_the_stable_items(): void
    {
        $items = app(PanelNavigation::class)->items();

        $this->assertSame(
            ['dashboard', 'administration', 'website'],
            array_map(static fn (NavigationItem $item): string => $item->key, $items),
        );
        $this->assertSame(
            ['users'],
            array_map(static fn (NavigationItem $item): string => $item->key, $items[1]->visibleChildren()),
        );
    }

    public function test_applications_can_register_navigation_items(): void
    {
        $navigation = new PanelNavigation;
        $navigation->register(NavigationItem::link(
            'application-item',
            'Application item',
            'bi bi-grid',
            static fn (): string => '/application-item',
        ));

        $this->assertSame(
            ['dashboard', 'administration', 'website', 'application-item'],
            array_map(static fn (NavigationItem $item): string => $item->key, $navigation->items()),
        );
    }

    public function test_applications_can_extend_the_administration_section(): void
    {
        $navigation = new PanelNavigation;
        $navigation->extend('administration', NavigationItem::link(
            'audit-log',
            'Audit log',
            'bi bi-list-check',
            static fn (): string => '/audit-log',
        ));

        $this->assertSame(
            ['users', 'audit-log'],
            array_map(
                static fn (NavigationItem $item): string => $item->key,
                $navigation->items()[1]->visibleChildren(),
            ),
        );
    }

    public function test_invalid_extensions_are_rendered_as_navigation_warnings(): void
    {
        $navigation = new PanelNavigation;
        $navigation->extend('missing-section', NavigationItem::link(
            'audit-log',
            'Audit log',
            'bi bi-list-check',
            static fn (): string => '/audit-log',
        ));

        $this->assertSame(
            'navigation-warning-missing-section',
            $navigation->items()[3]->key,
        );
        $this->assertTrue($navigation->items()[3]->isNotice());
    }

    public function test_navigation_views_are_not_published_as_application_overrides(): void
    {
        $paths = LaravelServiceProvider::pathsToPublish(ServiceProvider::class, 'mfw-views');

        $this->assertSame([], $paths);
    }
}
