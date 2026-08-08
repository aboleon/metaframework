<?php

declare(strict_types=1);

namespace Tests\Unit\Navigation;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
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

    public function test_applications_can_override_the_users_navigation_route(): void
    {
        Route::get('custom-users', static fn (): string => '')->name('custom.users');
        config([
            'mfw.navigation.users_route' => 'custom.users',
            'mfw.navigation.users_route_parameters' => [],
        ]);
        Route::getRoutes()->refreshNameLookups();

        $users = collect((new PanelNavigation)->items()[1]->visibleChildren())
            ->firstWhere('key', 'users');

        $this->assertNotNull($users);
        $this->assertSame(route('custom.users'), $users->url());
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

    public function test_administration_is_rendered_after_the_application_slot(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-mfw::nav-sidebar>
                <li class="mfw-nav-item"><span>Application menu</span></li>
            </x-mfw::nav-sidebar>
            BLADE);

        $this->assertLessThan(
            strpos($html, __('mfw::mfw.nav.administration')),
            strpos($html, 'Application menu'),
        );
    }

    public function test_website_is_rendered_immediately_before_administration(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-mfw::nav-sidebar>
                <li class="mfw-nav-item"><span>Application menu</span></li>
            </x-mfw::nav-sidebar>
            BLADE);

        $applicationPosition = strpos($html, 'Application menu');
        $websitePosition = strpos($html, __('mfw::mfw.nav.website'));
        $administrationPosition = strpos($html, __('mfw::mfw.nav.administration'));

        $this->assertIsInt($applicationPosition);
        $this->assertIsInt($websitePosition);
        $this->assertIsInt($administrationPosition);
        $this->assertLessThan($websitePosition, $applicationPosition);
        $this->assertLessThan($administrationPosition, $websitePosition);
    }

    public function test_navigation_views_are_not_published_as_application_overrides(): void
    {
        $paths = LaravelServiceProvider::pathsToPublish(ServiceProvider::class, 'mfw-views');

        $this->assertSame([], $paths);
    }

    public function test_dev_menu_does_not_include_role_management_links(): void
    {
        $view = File::get(dirname(__DIR__, 3).'/src/Resources/views/components/dev-menu.blade.php');

        $this->assertStringNotContainsString("route('mfw.users.index'", $view);
        $this->assertStringNotContainsString("route('mfw.role-groups.index'", $view);
        $this->assertStringNotContainsString("route('mfw.roles.index'", $view);
    }

    public function test_dev_menu_starts_with_the_artisan_command(): void
    {
        $view = File::get(dirname(__DIR__, 3).'/src/Resources/views/components/dev-menu.blade.php');
        $artisanPosition = strpos($view, 'id="mfw-nav-dev-artisan"');
        $migrationPosition = strpos($view, 'id="mfw-nav-dev-migrate"');

        $this->assertIsInt($artisanPosition);
        $this->assertIsInt($migrationPosition);
        $this->assertLessThan($migrationPosition, $artisanPosition);
    }

    public function test_dev_menu_uses_compact_font_size(): void
    {
        $css = File::get(dirname(__DIR__, 3).'/publishables/public/vendor/mfw/css/mfw-nav-sidebar.css');

        $this->assertStringContainsString('.mfw-nav-dev-menu > .mfw-nav-link', $css);
        $this->assertStringContainsString('.mfw-nav-dev-menu .mfw-nav-sublink', $css);
        $this->assertStringContainsString('font-size: 12px;', $css);
    }
}
