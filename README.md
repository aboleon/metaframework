# Meta Framework
Sub Framework pour Laravel 
### Installation

```bash
composer require aboleon/metaframework`

php artisan vendor:publish --tag=mfw
```
### Utilisation

#### Components
    
```blade
<x-mfw-input::input name="phone" :value="$data->phone" />
```

#### Translatable Tabs (Flat Declarations)

Flat translatable declarations (`key => label`) default to an input field.

```php
// In your model
public function setTranslatables(): array
{
    return [
        'access_instructions' => 'Access instructions',
    ];
}
```

```blade
<x-mfw::translatable-tabs :model="$data"/>
```

### Mediaclass Upload Library
Après une MAJ des fichiers JS ou traduction :
```
php artisan vendor:publish --tag=mfw-mediaclass --force
```

### License

The Metaframework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
