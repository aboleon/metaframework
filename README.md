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

### 5. **Admin User & Seeds**

The installer can also generate an admin user seeder and enhanced user factory. When prompted, provide the admin’s first name, last name, email, and password (leave blank to auto-generate). You can choose which role should be assigned to that admin.

The generated seeder writes the selected role into `users_roles` for the created admin account.

After the wizard completes, run:
```bash
php artisan migrate --seed
```
to set up the database and seed the admin user.

---

### 6. **Development/Maintenance Nav View**

MetaFramework includes a maintenance navigation view (`dev.blade.php`) for common development operations:
- Database migrations / rollback
- Application cache reset

**Publish for customization:**
```bash
php artisan mfw views
```

Or:
```bash
php artisan vendor:publish --provider="MetaFramework\ServiceProvider" --tag="mfw-views"
```

Published to: `resources/views/vendor/mfw/nav/dev.blade.php`

**Include in layout:**
```blade
@include('vendor.mfw.nav.dev')  {{-- published version --}}
@include('mfw::nav.dev')         {{-- package version --}}
```

**Note:** Uses `@role('dev|super-admin')` directive for role-based visibility.

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
- `roles` table (role catalog)
- `users_roles` table (user/role assignments)

Core system roles are reserved and always available:
- `dev` (`id: 1`)
- `super-admin` (`id: 2`)

Roles can be managed in the back-office at:
- `route('mfw.roles.index')`

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

## Optional Feature: UserType Segregation (`system` / `account`)

Use this only if your application stores multiple auth domains in the same `users` table.

### 1) Configure `config/mfw-user-types.php`

```php
return [
    'enabled' => true,
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

### 3) Optional typed model variants

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

When `enabled=false`, this feature is a no-op and credentials are not altered.

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
