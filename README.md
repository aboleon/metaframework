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

### 5. **Admin User & Seeds**

The installer can also generate an admin user seeder and enhanced user factory. When prompted, provide the admin’s first name, last name, email, and password (leave blank to auto-generate). You can choose which role (from `config/mfw-users.php`) should be assigned to that admin.

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
@include('mfw::dev')             {{-- package version --}}
```

**Note:** Uses `@role('dev')` directive for role-based visibility.

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
