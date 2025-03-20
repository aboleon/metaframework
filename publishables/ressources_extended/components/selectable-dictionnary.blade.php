@props([
    'required' => false,
    'alphaSort' => false,
])
<x-mfw::select :values="\App\Accessors\Dictionnaries::selectValues($key, ['alphaSort' => $alphaSort])"
               :name="$name"
               :label="\App\Accessors\Dictionnaries::title($key) . ($required ? ' *' : '')"
               :affected="$affected"
               :nullable="true"
               :group="\App\Accessors\Dictionnaries::type($key) == 'meta'"/>
