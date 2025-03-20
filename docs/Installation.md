# MetaFramework Installation Instructions

This guide provides step-by-step instructions for installing and configuring the **MetaFramework** using the provided `Install.php` console command.

---

## Prerequisites

Before proceeding, ensure that your environment meets the following requirements:
- PHP >= 8.4
- Laravel >= 11.x
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

3. **Update `RouteServiceProvider.php`**:
   - The `RouteServiceProvider.php` file will be updated to use the provided back-office route prefix.

---

### 4. **Publish Configuration Files**

The installer will automatically publish the necessary configuration files using the following command:
```bash
php artisan vendor:publish --provider="MetaFramework\MetaFrameworkServiceProvider" --tag="config"
```

---

### 5. **Verify Installation**

After the installation is complete, verify the following:

1. **Check `app.php`**:
   - Ensure the `name`, `locale`, and `fallback_locale` values are correctly updated.

2. **Check `mfw.php`**:
   - Ensure the file exists in the `config` directory and contains the correct values.

3. **Check `RouteServiceProvider.php`**:
   - Ensure the back-office route prefix is correctly applied.

---

## Troubleshooting

### 1. **File Permission Issues**
If the installer fails to update or create files, ensure that the following directories have the correct permissions:
- `config/`
- `app/Providers/`

### 2. **Missing `callPublishConfiguration` Method**
If you encounter an error related to the `callPublishConfiguration` method, ensure that the `MetaFrameworkServiceProvider` is correctly registered in your `config/app.php` file.

### 3. **Invalid Input**
If you provide invalid input (e.g., empty app name or locale), the installer will display an error message. Re-run the command and provide valid input.

---

## Uninstallation

To uninstall the MetaFramework, manually remove the following:
1. The `mfw.php` configuration file from the `config` directory.
2. Any changes made to `app.php` and `RouteServiceProvider.php`.

---

## Support

For further assistance, refer to the [MetaFramework Documentation](#) or contact the support team.

---

**Note**: This installation process assumes that the `Install.php` command is part of a Laravel package. If the package is not yet installed, ensure you install it via Composer before running the command.