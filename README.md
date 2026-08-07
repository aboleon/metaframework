# Meta Framework

Utility Sub Framework pour Laravel :: This is a personnal project with frequent changes, prototype state; use with caution at your own risk

### Installation

```bash
composer require aboleon/metaframework`

```
# MetaFramework Installation Instructions

This guide provides step-by-step instructions for installing and configuring the **MetaFramework** using the provided `Install.php` console command.

---

## Prerequisites

Before proceeding, ensure that your environment meets the following requirements:
- PHP >= 8.3
- Laravel - 11.x, 12.x
- Composer installed

---

## Installation Steps

### 1. **Run the Install Command**

To begin the installation, run the following Artisan command:
```bash
php artisan mfw config
```
This command will guide you through the configuration process.

---

### 2. **Provide Configuration Details**

During the installation, you will be prompted to provide the following details:

1. **App Name**:
    - Enter the name of your application.
    - Example: `My Awesome App`

2. **Default Language Locale**:
    - Enter the default language locale for your application (e.g., `en`, `fr`, `de`).
    - Default: `en`

3. **Back-Office Route Prefix**:
    - Enter the prefix for your back-office routes.
    - Example: `admin`

---

### 3. **Configuration Files**

The installer will perform the following actions:

1. **Update `app.php` Configuration**:
    - The `app.php` configuration file will be updated with the provided app name and default locale.

2. **Create `mfw.php` Configuration**:
    - A new configuration file (`mfw.php`) will be created in the `config` directory with the following structure:
      ```php
      return [
          'route' => 'admin', // Your provided back-office route prefix
          'locales' => ['en'], // Your provided default locale
          'active_locales' => ['en'] // Your provided default locale
      ];
      ```

3. **Update Routes**:
    - The dashboard route will be updated to use the provided back-office route prefix.

---

### 4. **Publish Configuration Files**

The installer will automatically publish the necessary configuration files using:
```bash
php artisan vendor:publish --provider="MetaFramework\ServiceProvider" --tag="mfw-install"
```

---

### 4.1 **Publish Language Files (Force Overwrite)**

To (re)publish MetaFramework language files and overwrite existing files in your app:
```bash
php artisan vendor:publish --tag="mfw-lang" --force
```

---

### 4.2 **Publish Auth Sub-Package (Optional)**

MetaFramework ships an auth scaffold as a publishable sub-package (`mfw-auth`).

Publish directly:
```bash
php artisan vendor:publish --provider="MetaFramework\ServiceProvider" --tag="mfw-auth"
```

Or use the helper command (publishes files with overwrite enabled and wires auth routes in `routes/web.php`):
```bash
php artisan mfw auth
```

Published auth files include:
- `app/Http/Controllers/Auth/*`
- `app/Http/Requests/Auth/LoginRequest.php`
- `app/View/Components/GuestLayout.php`
- `resources/views/auth/*`
- `routes/auth.php`
- `lang/en/mfw-auth.php` and `lang/fr/mfw-auth.php`
- `public/front/css/auth.css`

---

### 5. **Admin User & Seeds**

The installer can also generate an admin user seeder and enhanced user factory. When prompted, provide the admin’s first name, last name, email, and password (leave blank to auto-generate). You can choose which role should be assigned to that admin.

The generated seeder writes the selected role into `users_roles` for the created admin account.

After the wizard completes, run:
```bash
php artisan migrate --seed
```
to set up the database and seed the admin user.

---

### 6. **Development/Maintenance Navigation**

The back-office navigation is package-owned so updates are shared by every application:

```blade
<x-mfw::nav-sidebar />
```

The component includes the dashboard, website link, administration links, and the role-protected development menu. The package always renders `Administration` immediately before `Dev`; applications should extend the section rather than reorder these shared items. On desktop, the sidebar has auto height and remains in normal document flow; the page provides the only scrollbar, so expanded Administration content pushes Dev downward without clipping it. Applications may place their own menu items in the component slot. Do not publish or override the package navigation views.

Applications can add links inside a package section from their `AppServiceProvider` without replacing the package view:

```php
use MetaFramework\Navigation\NavigationItem;
use MetaFramework\Navigation\PanelNavigation;

public function boot(PanelNavigation $navigation): void
{
    $navigation->extend('administration', NavigationItem::link(
        'audit-log',
        __('app.nav.audit_log'),
        'bi bi-list-check',
        static fn (): string => route('panel.audit.index'),
    ));
}
```

The item is appended to the existing `Administration` section, after Users, Messages, and Log Viewer. Use `NavigationItem::section()` with `register()` for a new top-level application section. Visibility can be controlled with the optional visibility closure. If the section key is invalid, the extension is ignored and a visible navigation warning is added instead of interrupting the application.

The navigation assets are published to `public/vendor/mfw`. Because published files are copies, applications should either run this after a package update:

```bash
php artisan vendor:publish --tag=mfw-navigation-assets --force
```

or add the same command to the consuming application's Composer `post-update-cmd` hook. The package cannot modify the root application's Composer scripts automatically. The broader `mfw-assets` tag remains available for an explicit full package-asset publish, but should not be used as an automatic update hook when an application has custom MFW CSS.

The core Users item targets `mfw.users.index` with the `super-admin` parameter by default. Applications with an existing Users screen can preserve it by overriding `mfw.navigation.users_route` and `mfw.navigation.users_route_parameters` in their application configuration.

## Roles & Access Management

### 1) Enable role capabilities on your User model

```php
use MetaFramework\Traits\Users;

class User extends Authenticatable
{
    use Users;
}
```

### 2) Data model

Role access is database-driven with:
- `role_groups` table (role group catalog)
- `roles` table (role catalog)
- `users_roles` table (user/role assignments)

Only `dev` and `super-admin` are core access roles. Role groups are optional and serve as app-level classification/refinement.

Core system roles are reserved and always available:
- `dev` (`id: 1`)
- `super-admin` (`id: 2`)

Roles can be managed in the back-office at:
- `route('mfw.role-groups.index')`
- `route('mfw.roles.index')`
- System users listing: `route('mfw.users.index', 'super-admin')`

Published role migration stubs (install baseline):
- `publishables/database/migrations/2022_05_15_214450_create_role_groups_table.php`
- `publishables/database/migrations/2022_05_15_214500_create_roles_table.php`
- `publishables/database/migrations/2022_05_15_214516_create_user_roles_table.php`

### 3) Usage in code and Blade

Model checks:
```php
$user->hasRole('dev');
$user->hasRole('dev|super-admin');
$user->hasRole(['dev', 'super-admin']);
$user->hasRole('2'); // by role id
```

Blade checks:
```blade
@role('dev|super-admin')
    ...
@endrole
```

### 4) Fresh install fallback behavior

If `users_roles` has no assignment at all (fresh installation), `hasRole()` falls back to authenticated access for protected checks.
As soon as at least one role assignment exists in database, strict role checks are applied.

## Core UserType Segregation (`system` / `account`)

MetaFramework supports separating auth domains on the same `users` table via a `type` discriminator.

When using `aboleon/metaframework-accounts`, the `config/mfw-user-types.php` file is now owned/published by the accounts package (not by `metaframework`). MetaFramework still provides the runtime helpers (`MetaFramework\Support\UserTypes`, `MetaFramework\Traits\TypedUser`) that consume this configuration.

### 1) Configure `config/mfw-user-types.php`

```php
return [
    'enabled' => true, // core behavior (can still be disabled explicitly)
    'column' => 'type',
    'values' => ['system', 'account'],
    'default' => 'system',
    'guards' => [
        'web' => 'system',
        'account' => 'account',
    ],
];
```

### 2) Build guard-aware credentials

```php
use MetaFramework\Support\UserTypes;

$credentials = UserTypes::addToCredentials(
    $request->only('email', 'password'),
    guard: 'account'
);
```

### 3) Typed model variants

```php
use MetaFramework\Traits\TypedUser;

class SystemUser extends User
{
    use TypedUser;

    protected static function typedUserScopeType(): ?string
    {
        return 'system';
    }
}

class AccountUser extends User
{
    use TypedUser;

    protected static function typedUserScopeType(): ?string
    {
        return 'account';
    }
}
```

When `enabled=false`, credentials are not altered and typed scopes are skipped.

## Administration Nav Links

Typical administration submenu links:

```blade
<a href="{{ route('mfw.users.index', 'super-admin') }}">{{ __('mfw-users.users.nav') }}</a>
<a href="{{ route('mfw.role-groups.index') }}">{{ __('mfw-users.role_groups.nav') }}</a>
<a href="{{ route('mfw.roles.index') }}">{{ __('mfw-users.roles.nav') }}</a>
```

**Required for AJAX actions:** place this container in a convenient spot in your app layout so the nav actions can post to MFW Ajax:
```blade
<div id="mfw-messages" data-ajax="{{route('mfw-ajax') }}"></div>
```

---

## ArtisanController UI Commands

The maintenance UI triggers `MetaFramework\Controllers\ArtisanController` to run a small set of safe commands:

- `optimize:clear`
- `migrate` (forced, non-interactive)
- `migrate:rollback` (forced, non-interactive)
- `composer update` (production or dev flags depending on the action)

**PHP path override for Composer**: if the server PHP binary is not in PATH (example error: `/usr/bin/env: 'php': No such file or directory`), set `MF_SHELL_PATH_PREFIX` to a PHP bin path to prepend before running Composer. Example: `MF_SHELL_PATH_PREFIX=/opt/plesk/php/8.5/bin`.

---

## Troubleshooting

- **File Permission Issues**: ensure `config/` and `database/` are writable.
- **Missing Methods**: confirm the package service provider is registered.
- **Invalid Input**: re-run the command and provide valid values.

---
## Cyrillic Content Utilities

MetaFramework provides `MetaFramework\Polyglote\Traits\CyrillicContentTrait` for reusable Cyrillic checks.

- `hasCyrillic(string $value): bool` detects Cyrillic characters in a string.
- `isCyrillicLocale(?string $locale): bool` checks whether a locale uses Cyrillic script (supports region variants like `bg_BG`).

Example usage:
```php
use MetaFramework\Polyglote\Traits\CyrillicContentTrait;

class Example
{
    use CyrillicContentTrait;
}
```

---

## Uninstallation

Remove `config/mfw.php`, revert any changes to `config/app.php`, and delete published resources as needed.

---
### License

The Metaframework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
