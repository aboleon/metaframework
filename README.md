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

### Mediaclass Upload Library
Après une MAJ des fichiers JS ou traduction :
```
php artisan vendor:publish --tag=mfw-mediaclass --force
```

### License

The Metaframework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

